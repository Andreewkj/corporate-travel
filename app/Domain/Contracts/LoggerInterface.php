<?php

declare(strict_types=1);

namespace App\Domain\Contracts;

interface LoggerInterface
{
    public function error(string $message): void;
    public function info(string $message): void;
    public function warning(string $message): void;
    public function critical(string $message): void;
}
