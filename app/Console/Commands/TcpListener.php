<?php

namespace App\Console\Commands;

use App\Models\VoipRecord;
use Illuminate\Console\Command;
use Socket;
class TcpListener extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tcp:listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a TCP listener that listens for incoming connections on a specified port.';

    protected $port = 2533;
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $host = '0.0.0.0';

        if (!extension_loaded('sockets')) {
            $this->error("Sockets extension is NOT enabled.");
            return;
        }

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (!$socket) {
            $this->error("Socket creation failed: " . socket_strerror(socket_last_error()));
            return;
        }

        socket_bind($socket, $host, $this->port);
        socket_listen($socket);
        $this->info("TCP listener started on {$host}:{$this->port}");

        while (true) {
            $client = @socket_accept($socket);
            if ($client === false) {
                $this->error("Socket accept failed: " . socket_strerror(socket_last_error()));
                continue;
            }

            socket_set_option($client, SOL_SOCKET, SO_RCVTIMEO, ["sec" => 10, "usec" => 0]);

            try {
                while (true) {
                    $chunk = socket_read($client, 2048, PHP_NORMAL_READ);
                    if ($chunk === false || $chunk === '') {
                        break;
                    }

                    $line = trim($chunk);
                    if ($line === '') continue;

                    file_put_contents(storage_path('logs/tcp_listener.log'), $line . PHP_EOL, FILE_APPEND);

                    VoipRecord::create($line); // Use correct column name
                }

                socket_write($client, "ACK\n");
            } catch (\Exception $e) {
                $this->error("Client handling error: " . $e->getMessage());
            }

            socket_close($client);
        }

        socket_close($socket);
    }
}
