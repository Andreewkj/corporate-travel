<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Infra\Messaging\Consumers\NotifyConsumer;
use App\Infra\Messaging\RabbitMQChannelFactory;
use Illuminate\Console\Command;

class RunNotifyConsumer extends Command
{
    private const EXCHANGE = 'travel_request_notifications';

    protected $signature = 'consumer:notify';
    protected $description = 'Init consumer to notify users';

    public function __construct(
        protected NotifyConsumer $notifyConsumer,
        protected RabbitMQChannelFactory $channelFactory
    )
    {
        parent::__construct();
    }

    public function handle()
    {
        $channel = $this->channelFactory->makeWithMultipleQueues([
            'notify_email' => ['routing_key' => 'email'],
        ], self::EXCHANGE);

        $channel->basic_consume('notify_email', '', false, false, false, false, [$this->notifyConsumer, 'consumeEmail']);

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $channel->getConnection()->close();
    }
}
