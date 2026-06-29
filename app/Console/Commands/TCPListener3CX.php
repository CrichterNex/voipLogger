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
                    if ($line === '')  {
                        continue;
                    }

                    file_put_contents(storage_path('logs/tcp_listener3cx.log'), "$line" . PHP_EOL, FILE_APPEND);
                    

                    ThreeCXCDR::PreProcessData($line);

                    
                }

                //socket_write($client, "ACK\n");
            } catch (\Exception $e) {
                $this->error("Client handling error: " . $e->getMessage());
                Storage::append('logs/tcp_listener3cx_errors.log', "Client handling error: " . $e->getMessage() . "\n");
            }

            socket_close($client);
        }

        socket_close($socket);
    }
}
