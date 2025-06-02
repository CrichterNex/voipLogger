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
        
        if (extension_loaded('sockets')) {
            echo "Sockets extension is enabled.\n";
        } else {
           $this->error("Sockets extension is NOT enabled.\n");
           return;
        }
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if(!$socket) {
            $this->error("Socket creation failed: " . socket_strerror(socket_last_error()));
            return;
        }
        socket_bind($socket, $host, $this->port);
        socket_listen($socket);

        $this->info("TCP listener started on {$host}:{$this->port}");

        while (true) {
            $client = socket_accept($socket);
            if ($client === false) {
                $this->error("Failed to accept connection: " . socket_strerror(socket_last_error()));
                continue;
            }

            $buffer = '';
            while (true) {
                $chunk = socket_read($client, 2048, PHP_NORMAL_READ);
                if ($chunk === false || $chunk === '') {
                    // Connection closed or error
                    break;
                }

                $chunk = trim($chunk); // Remove trailing newlines
                if ($chunk === '') continue; // Ignore empty lines

                $buffer .= $chunk . "\n"; // Accumulate data

                // Log each chunk (optional, or log after full message)
                file_put_contents(storage_path('logs/tcp_listener.log'), "$chunk\n", FILE_APPEND);

                try {
                    // Example: convert to array or process if it's JSON or key-value
                    $data = ['data' => $chunk];
                    VoipRecord::create($data);
                } catch (\Exception $e) {
                    $this->error("Failed to create VoipRecord in DB: " . $e->getMessage() . " -- Data: $chunk");
                }
            }

            socket_write($client, "ACK\n");
            socket_close($client);
        }

        socket_close($socket);
    }
}
