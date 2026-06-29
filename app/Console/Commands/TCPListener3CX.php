<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ThreeCXCDR;
use Illuminate\Support\Facades\Log;

class TcpListener3CX extends Command
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
    protected $description = 'Creates a TCP listener that listens for incoming connections on a specified port.';

    /**
     * Listener port.
     *
     * @var int
     */
    protected $port = 3000;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $host = '0.0.0.0';

        if (!extension_loaded('sockets')) {
            $this->error('Sockets extension is NOT enabled.');
            return 1;
        }

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if ($socket === false) {
            $this->error(
                'Socket creation failed: ' .
                socket_strerror(socket_last_error())
            );
            return 1;
        }

        socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);

        if (!socket_bind($socket, $host, $this->port)) {
            $this->error(
                'Socket bind failed: ' .
                socket_strerror(socket_last_error($socket))
            );
            socket_close($socket);
            return 1;
        }

        if (!socket_listen($socket, 128)) {
            $this->error(
                'Socket listen failed: ' .
                socket_strerror(socket_last_error($socket))
            );
            socket_close($socket);
            return 1;
        }

        $this->info("TCP listener started on {$host}:{$this->port}");

        while (true) {
            try {
                $this->line('Waiting for connection...');

                $client = socket_accept($socket);

                if ($client === false) {
                    $error = socket_last_error($socket);

                    $this->error(
                        'Socket accept failed: ' .
                        socket_strerror($error)
                    );

                    sleep(1);
                    continue;
                }

                socket_getpeername($client, $clientIp, $clientPort);

                $this->info(
                    "Client connected: {$clientIp}:{$clientPort}"
                );

                socket_set_option(
                    $client,
                    SOL_SOCKET,
                    SO_RCVTIMEO,
                    [
                        'sec' => 10,
                        'usec' => 0,
                    ]
                );

                $recordCount = 0;

                while (true) {
                    $chunk = socket_read(
                        $client,
                        2048,
                        PHP_NORMAL_READ
                    );

                    if ($chunk === false) {
                        $error = socket_last_error($client);

                        $this->warn(
                            'Read failed: ' .
                            socket_strerror($error)
                        );

                        break;
                    }

                    if ($chunk === '') {
                        $this->line(
                            "Client disconnected: {$clientIp}:{$clientPort}"
                        );
                        break;
                    }

                    $line = trim($chunk);

                    if (empty($line)) {
                        continue;
                    }

                    $recordCount++;

                    $this->line(
                        "Processing record #{$recordCount}"
                    );

                    try {
                        file_put_contents(
                            storage_path('logs/tcp_listener.log'),
                            date('Y-m-d H:i:s') .
                            ' ' .
                            $line .
                            PHP_EOL,
                            FILE_APPEND | LOCK_EX
                        );

                        $start = microtime(true);

                        ThreeCXCDR::PreProcessData($line);

                        $duration = round(
                            microtime(true) - $start,
                            3
                        );

                        $this->info(
                            "Record #{$recordCount} processed in {$duration}s"
                        );
                    } catch (\Throwable $e) {
                        $message =
                            "Record processing failed: " .
                            $e->getMessage();

                        $this->error($message);

                        Log::error($message, [
                            'exception' => $e,
                            'record' => $line,
                        ]);

                        continue;
                    }
                }

                @socket_write($client, "ACK\n");

                socket_close($client);

                $this->info(
                    "Connection closed. Records processed: {$recordCount}"
                );
            } catch (\Throwable $e) {
                $this->error(
                    'Listener error: ' . $e->getMessage()
                );

                Log::error('TCP Listener Error', [
                    'exception' => $e,
                ]);

                sleep(1);
            }
        }

        socket_close($socket);

        return 0;
    }
}