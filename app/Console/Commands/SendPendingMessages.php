<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class SendPendingMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-pending-messages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar mensajes pendientes a RabbitMQ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $pendingMessages = Message::where('sent', false)->get();
        foreach ($pendingMessages as $message) {
            $this->tryToSendMessage($message);
        }
    }

    private function tryToSendMessage($message)
    {
        $host = '127.0.0.1'; // o tu host de RabbitMQ
        $port = 5672; // el puerto estándar de RabbitMQ
        $user = 'guest'; // usuario predeterminado de RabbitMQ
        $password = 'guest'; // contraseña predeterminada de RabbitMQ
        $queueName = 'nombre_de_tu_cola'; // el nombre de tu cola
    
        try {
            // Crear una conexión a RabbitMQ
            $connection = new AMQPStreamConnection($host, $port, $user, $password);
            $channel = $connection->channel();
    
            // Declarar una cola
            $channel->queue_declare($queueName, false, true, false, false);
    
            // Crear un mensaje
            $msg = new AMQPMessage($message->body);
    
            // Publicar el mensaje en la cola
            $channel->basic_publish($msg, '', $queueName);
    
            // Marcar el mensaje como enviado
            $message->sent = true;
            $message->save();
    
            // Cerrar la conexión y el canal
            $channel->close();
            $connection->close();
        } catch (\Exception $e) {
            \Log::error("Error al enviar mensaje a RabbitMQ: " . $e->getMessage());
        }
    }
}
