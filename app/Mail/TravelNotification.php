<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TravelNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(private string $notificationMessage, private array $payload = []) {}

    public function build(): self
    {
        return $this->subject('Atualização do pedido de viagem')
            ->view('emails.travel_notification')
            ->with([
                'notificationMessage' => $this->notificationMessage,
                'payload' => $this->payload,
            ]);
    }
}
