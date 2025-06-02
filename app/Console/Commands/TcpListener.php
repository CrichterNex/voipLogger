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

            $input = socket_read($client, 2048000);

            //Log data to a file
            file_put_contents(storage_path('logs/tcp_listener.log'), "$input\n", FILE_APPEND);

            // Example: Save to database or dispatch a job
            // \App\Models\YourModel::create(['data' => $input]);
            try {
                if (empty($input)) {
                    //ignore
                } else { 
                    $input = explode("\r\n", $input);
                    $this->info("Received: $input");
                    
                    
                }
            } catch (\Exception $e) {
                $this->error("Failed to create VoipRecord in DB: " . $e->getMessage());
            }

            socket_write($client, "ACK\n");
            socket_close($client);
        }

        socket_close($socket);
    }
}
