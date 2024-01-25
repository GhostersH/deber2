<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQConsumer extends Command
{
    protected $signature = 'rabbitmq:consume';
    protected $description = 'Consume messages from RabbitMQ';

    public function handle()
    {
        $connection = new AMQPStreamConnection('127.0.0.1', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        $channel->queue_declare('nombre_de_tu_cola', false, true, false, false);

        $callback = function ($msg) {
            try {
                $datosMensaje = json_decode($msg->body);
                
                if ($datosMensaje && isset($datosMensaje->esExitoso) && $datosMensaje->esExitoso) {
                    // Procesar solo si el mensaje es exitoso
                    echo ' [x] Received ', $msg->body, "\n";
                    $msg->ack(); // Confirmar el mensaje.
                }
            } catch (\Exception $e) {
                // Manejar errores, por ejemplo, errores de decodificación JSON.
            }
        };

        $channel->basic_consume('nombre_de_tu_cola', '', false, false, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}

