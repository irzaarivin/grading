<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

use App\Http\Controllers\TaskController;

class RabbitMQ {

    protected $exchange = "ERP-Exchange";
    protected $routeMap = [
        "task" => [
            "class" => TaskController::class,
            "function" => 'consumeRabbit'
        ]
    ];
    
    public function publish($queue, $message) {
        $connection = new AMQPStreamConnection(env('MQ_HOST'), env('MQ_PORT'), env('MQ_USER'), env('MQ_PASS'), env('MQ_VHOST'));

        $channel = $connection->channel();
        $channel->exchange_declare($this->exchange, 'direct', false, false, false);
        // $channel->exchange_declare($this->exchange, 'x-delayed-message', false, true, false, false, false, new AMQPTable(array(
        //     'x-delayed-type' => AMQPExchangeType::DIRECT
        // )));
        $channel->queue_declare($queue, false, false, false, false);
        // $channel->queue_declare($queue, false, false, false, false, false, new AMQPTable(array(
        //     'x-dead-letter-exchange' => 'delayed'
        // )));
        $channel->queue_bind($queue, $this->exchange);

        $msg = new AMQPMessage($message);
        $channel->basic_publish($msg, $this->exchange, $queue);

        $channel->close();
        $connection->close();
    }

    public function consume($queue) {
        $connection = new AMQPStreamConnection(env('MQ_HOST'), env('MQ_PORT'), env('MQ_USER'), env('MQ_PASS'), env('MQ_VHOST'));
        $channel = $connection->channel();

        $callback = function ($msg) {
            TaskController::consumeRabbit($msg);
        };

        $channel->queue_declare($queue, false, false, false, false);
        $channel->basic_consume($queue, '', false, true, false, false, $callback);
        echo 'Listening gan...', " \n";
        while ($channel->is_consuming()) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
    }

}