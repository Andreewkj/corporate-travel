<?php

declare(strict_types=1);

namespace App\Infra\Messaging;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class MessageBusPublisher
{
    private const EXCHANGE = 'travel_request_notifications';

    protected ?AMQPChannel $channel = null;

    public function __construct(protected RabbitMQConnectionFactory $connectionFactory) {}

    public function publishNotification(array $payload): void
    {
        $email = $payload['user_email'] ?? null;

        if (! $email) {
            return;
        }

        $emailPayload = json_encode([
            'travel_request_id' => $payload['id'] ?? null,
            'status' => $payload['status'] ?? null,
            'message' => $payload['message'] ?? "Seu pedido de viagem foi atualizado",
            'email' => $email,
        ]);

        $this->publish('email', $emailPayload);
    }

    protected function publish(string $routingKey, string $body): void
    {
        $msg = new AMQPMessage($body, [
            'delivery_mode' => 2
        ]);

        $this->channel()->basic_publish($msg, self::EXCHANGE, $routingKey);
    }

    protected function channel(): AMQPChannel
    {
        if ($this->channel) {
            return $this->channel;
        }

        $connection = $this->connectionFactory->getConnection();
        $this->channel = $connection->channel();

        $this->channel->exchange_declare(
            self::EXCHANGE,
            'direct',
            false,
            true,
            false
        );

        return $this->channel;
    }

    public function __destruct()
    {
        if ($this->channel) {
            $this->channel->close();
            $this->connectionFactory->close();
        }
    }
}
