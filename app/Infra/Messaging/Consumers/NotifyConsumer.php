<?php

declare(strict_types=1);

namespace App\Infra\Messaging\Consumers;

use App\Application\Services\NotifyUserService;
use App\Domain\Contracts\LoggerInterface;
use App\Infra\Messaging\RabbitMQChannelFactory;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

class NotifyConsumer
{
    public function __construct(
        private NotifyUserService $notifyUserService,
        private RabbitMQChannelFactory $channelFactory,
        private LoggerInterface $logger
    ) {}

    public function consumeEmail(AMQPMessage $msg): void
    {
        echo "Iniciando notificação por email para o usuário...\n";

        $body = json_decode($msg->getBody(), true);
        $headers = $msg->get_properties()['application_headers'] ?? null;
        $tries = $headers instanceof AMQPTable && isset($headers->getNativeData()['x-tries'])
            ? $headers->getNativeData()['x-tries']
            : 0;

        try {
            $this->notifyUserService->notifyByEmail($body);
            $msg->ack();
            echo "E-mail enviado com sucesso\n";
        } catch (\Throwable $e) {
            if ($tries >= 3) {
                echo "Enviando para DLQ... Motivo: {$e->getMessage()}\n";
                $this->sendToDlq('dlq_notify_email', $body, $e->getMessage());
                $msg->ack();

                $this->logger->info('Email was sent to DLQ after 3 retries. Error: ' . $e->getMessage());
                return;
            }

            echo "Erro ao enviar e-mail. Retentativa " . ($tries + 1) . ". Motivo: {$e->getMessage()}\n";
            $this->republishWithRetry($msg, 'notify_email', $tries + 1);
            $msg->ack();
        }
    }

    protected function republishWithRetry(AMQPMessage $msg, string $queue, int $tries): void
    {
        $msgBody = $msg->getBody();
        $msgProps = [
            'delivery_mode' => 2,
            'application_headers' => new AMQPTable([
                'x-tries' => $tries,
            ]),
        ];

        $channel = $msg->delivery_info['channel'];
        $channel->basic_publish(
            new AMQPMessage($msgBody, $msgProps),
            '',
            $queue
        );
    }

    protected function sendToDlq(string $queue, array $data, string $errorMessage): void
    {
        $channel = $this->channelFactory->make(
            queue: $queue,
            queueOptions: [],
            exchange: 'dlq.exchange',
            exchangeOptions: [],
            routingKey: $queue
        );

        $payload = [
            'original_data' => $data,
            'error' => $errorMessage,
            'timestamp' => now()->toDateTimeString(),
        ];

        $message = new AMQPMessage(json_encode($payload), [
            'delivery_mode' => 2
        ]);

        $channel->basic_publish($message, '', $queue);

        $channel->close();
    }
}
