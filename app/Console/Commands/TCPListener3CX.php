<?php

namespace App\Console\Commands;

use App\Models\ThreeCXCDR;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\Settings;
use Socket;
class TCPListener3CX extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tcp:listen-3cx';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'TCP listener for 3CX CDR data. Listens on port 3000 and processes incoming data.';
    protected $port = 3000;
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $host = '0.0.0.0';

        $allowed_ips = Settings::where('name', 'cdr_allowed_hosts')->first();
        if ($allowed_ips) {
            $allowed_ips = explode(',', $allowed_ips->value);       
        } else {
            $allowed_ips = [];
        }

        if (!extension_loaded('sockets')) {
            $this->error("Sockets extension is NOT enabled.");
            return;
        }

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$socket) {
            $this->error("Socket creation failed: " . socket_strerror(socket_last_error()));
            return;
        }

        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($socket, $host, $this->port);
        socket_listen($socket, 16);
        socket_set_nonblock($socket);
        $this->info("TCP listener started on {$host}:{$this->port}");

        // Keyed by socket id => ['socket' => Socket, 'buffer' => string]. A blocking,
        // one-client-at-a-time accept loop meant the persistent 3CX CDR connection
        // starved out anything else trying to reach this port (e.g. health checks) -
        // socket_select() lets us service every connected socket concurrently instead.
        $clients = [];

        while (true) {
            $read = [$socket];
            foreach ($clients as $c) {
                $read[] = $c['socket'];
            }

            $write = null;
            $except = null;
            $changed = @socket_select($read, $write, $except, 1);
            if ($changed === false || $changed === 0) {
                continue;
            }

            foreach ($read as $sock) {
                if ($sock === $socket) {
                    $client = @socket_accept($socket);
                    if ($client === false) {
                        continue;
                    }

                    socket_set_nonblock($client);

                    // Keep the TCP session generating traffic while idle so stateful
                    // firewalls (e.g. FortiGate) don't age out the connection during
                    // quiet overnight periods.
                    socket_set_option($client, SOL_SOCKET, SO_KEEPALIVE, 1);
                    if (defined('TCP_KEEPIDLE')) {
                        socket_set_option($client, SOL_TCP, TCP_KEEPIDLE, 30);
                    }
                    if (defined('TCP_KEEPINTVL')) {
                        socket_set_option($client, SOL_TCP, TCP_KEEPINTVL, 10);
                    }
                    if (defined('TCP_KEEPCNT')) {
                        socket_set_option($client, SOL_TCP, TCP_KEEPCNT, 3);
                    }

                    $clients[spl_object_id($client)] = ['socket' => $client, 'buffer' => ''];
                    continue;
                }

                $id = spl_object_id($sock);
                if (!isset($clients[$id])) {
                    continue;
                }

                $chunk = @socket_read($sock, 2048);
                if ($chunk === false) {
                    $errno = socket_last_error($sock);
                    if (in_array($errno, [SOCKET_EAGAIN, SOCKET_EWOULDBLOCK], true)) {
                        continue; // spurious wakeup, no data actually available yet
                    }
                    socket_close($sock);
                    unset($clients[$id]);
                    continue;
                }
                if ($chunk === '') {
                    // Peer closed the connection cleanly.
                    socket_close($sock);
                    unset($clients[$id]);
                    continue;
                }

                $clients[$id]['buffer'] .= $chunk;

                while (($pos = strpos($clients[$id]['buffer'], "\n")) !== false) {
                    $line = trim(substr($clients[$id]['buffer'], 0, $pos));
                    $clients[$id]['buffer'] = substr($clients[$id]['buffer'], $pos + 1);

                    if ($line === '') {
                        continue;
                    }

                    try {
                        file_put_contents(storage_path('logs/tcp_listener3cx.log'), "$line" . PHP_EOL, FILE_APPEND);

                        ThreeCXCDR::PreProcessData($line);
                    } catch (\Exception $e) {
                        $this->error("Client handling error: " . $e->getMessage());
                        Storage::append('logs/tcp_listener3cx_errors.log', "Client handling error: " . $e->getMessage() . "\n");
                    }
                }
            }
        }
    }
}
