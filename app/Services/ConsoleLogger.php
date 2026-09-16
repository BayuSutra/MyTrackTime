<?php

namespace App\Services;

trait ConsoleLogger
{
    protected bool $isConsoleMode = false;

    public function setConsoleMode(bool $mode): void
    {
        $this->isConsoleMode = $mode;
    }

    protected function log(string $message, string $type = 'info'): void
    {
        if ($this->isConsoleMode) {
            echo $message . "\n";
        }
    }

    protected function logSuccess(string $message): void
    {
        $this->log("✅ " . $message);
    }

    protected function logError(string $message): void
    {
        $this->log("❌ " . $message);
    }

    protected function logWarning(string $message): void
    {
        $this->log("⚠️ " . $message);
    }

    protected function logInfo(string $message): void
    {
        $this->log("📩 " . $message);
    }
}