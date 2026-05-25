<?php

declare(strict_types=1);

namespace App\Infra\Messaging;

use PhpAmqpLib\Channel\AMQPChannel;

class RabbitMQChannelFactory
{
    protected RabbitMQConnectionFactory $connectionFactory;

    public function __construct(RabbitMQConnectionFactory $connectionFactory)
    {
        $this->connectionFactory = $connectionFactory;
    }

    public function make(string $queue, array $queueOptions = [], string $exchange = '', array $exchangeOptions = [], string $routingKey = ''): AMQPChannel
    {
        $connection = $this->connectionFactory->getConnection();
        $channel = $connection->channel();

        if ($exchange) {
            $channel->exchange_declare(
                $exchange,
                $exchangeOptions['type'] ?? 'direct',
                $exchangeOptions['passive'] ?? false,
                $exchangeOptions['durable'] ?? true,
                $exchangeOptions['auto_delete'] ?? false
            );
        }

        $channel->queue_declare(
            $queue,
            $queueOptions['passive'] ?? false,
            $queueOptions['durable'] ?? true,
            $queueOptions['exclusive'] ?? false,
            $queueOptions['auto_delete'] ?? false
        );

        if ($exchange && $routingKey) {
            $channel->queue_bind($queue, $exchange, $routingKey);
        }

        return $channel;
    }

    public function makeWithMultipleQueues(array $queues, string $exchange, array $exchangeOptions = []): AMQPChannel
    {
        $connection = $this->connectionFactory->getConnection();
        $channel = $connection->channel();

        $channel->exchange_declare(
            $exchange,
            $exchangeOptions['type'] ?? 'direct',
            $exchangeOptions['passive'] ?? false,
            $exchangeOptions['durable'] ?? true,
            $exchangeOptions['auto_delete'] ?? false
        );

        foreach ($queues as $queue => $options) {
            $channel->queue_declare(
                $queue,
                $options['passive'] ?? false,
                $options['durable'] ?? true,
                $options['exclusive'] ?? false,
                $options['auto_delete'] ?? false
            );

            $channel->queue_bind($queue, $exchange, $options['routing_key'] ?? '');
        }

        return $channel;
    }
}
