<?php

declare(strict_types=1);

namespace App\Application\Services;

use Illuminate\Support\Facades\Mail;
use App\Mail\TravelNotification;

final class NotifyUserService
{
    public function notifyByEmail(array $data): void
    {
        $email = $data['email'] ?? null;
        $message = $data['message'] ?? 'Atualização no seu pedido de viagem';

        if (! $email) {
            throw new \InvalidArgumentException('Email is required to send notification');
        }

        Mail::to($email)->send(new TravelNotification($message, $data));
    }
}
