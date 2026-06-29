<?php

namespace App\Console\Commands;

use App\Models\VoipRecord;
use Illuminate\Console\Command;
use Socket;
use App\Models\MitelCDR;
use App\Models\ThreeCXCDR;

class TcpListener3CX extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tcp:listen-3cx'; // Change the command signature to tcp:listen-3cx

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a TCP listener that listens for incoming connections on a specified port.';

    protected $port = 3000; // Change the port to 3000
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

        if (!socket_bind($socket, $host, $this->port)) {
            $this->error("Bind failed: " . socket_strerror(socket_last_error($socket)));
            return 1;
        }

        if (!socket_listen($socket)) {
            $this->error("Listen failed: " . socket_strerror(socket_last_error($socket)));
            return 1;
        }
        try {
            $this->info("TCP listener started on {$host}:{$this->port}");

            while (true) {
                $client = @socket_accept($socket);
                if ($client === false) {
                    $this->error("Socket accept failed: " . socket_strerror(socket_last_error()));
                    continue;
                }

                socket_set_option($client, SOL_SOCKET, SO_RCVTIMEO, ["sec" => 10, "usec" => 0]);

                    while (true) {
                        $chunk = socket_read($client, 2048, PHP_NORMAL_READ);
                        if ($chunk === false) {
                            $err = socket_last_error($client);
                            $this->error("Socket error: " . socket_strerror($err));
                            break;
                        }

                        $line = trim($chunk);
                        if ($chunk === '') {
                            // DO NOT assume disconnect in streaming mode
                            continue;
                        }

                        file_put_contents(storage_path('logs/tcp_listener.log'), $line . PHP_EOL, FILE_APPEND);

                        ThreeCXCDR::PreProcessData($line); // Use correct column name

                    }

                    socket_write($client, "ACK\n");
                

                socket_close($client);
            }

            socket_close($socket);
        } catch (\Exception $e) {
            $this->error("Client handling error: " . $e->getMessage());
            \Illuminate\Support\Facades\Storage::append('logs/tcp_listener_errors.log', "Client handling error: " . $e->getMessage() . "\n");
        }
    }
}
