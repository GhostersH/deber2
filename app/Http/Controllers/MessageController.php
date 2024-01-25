<?php

namespace App\Http\Controllers; // Añadir el namespace correcto

use App\Models\Message;
use Illuminate\Http\Request;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class MessageController extends Controller // Extender la clase base Controller
{
    public function store(Request $request)
    {
        $message = new Message();
        $message->body = $request->body;
        $message->save();

        $this->tryToSendMessage($message);

        return response()->json(['message' => 'Mensaje recibido'], 200);
    }

    public function index()
    {
        $messages = Message::all(); // Obtiene todos los mensajes de la base de datos.
        return response()->json($messages); // Devuelve los mensajes en formato JSON.
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
            // Manejar la excepción en caso de error
            // Aquí puedes registrar el error o realizar alguna acción
        }
    }
}
