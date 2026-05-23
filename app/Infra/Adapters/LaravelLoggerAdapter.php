<?php

declare(strict_types=1);

namespace App\Infra\Adapters;

use App\Domain\Contracts\LoggerInterface;
use Illuminate\Support\Facades\Log;

final class LaravelLoggerAdapter implements LoggerInterface
{
    public function error(string $message): void
    {
        Log::error($message);
    }

    public function info(string $message): void
    {
        Log::info($message);
    }

    public function warning(string $message): void
    {
        Log::warning($message);
    }

    public function critical(string $message): void
    {
        Log::critical($message);
    }
}
