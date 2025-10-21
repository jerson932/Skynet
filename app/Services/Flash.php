<?php

namespace App\Services;

class Flash
{
    public function message(string $level, string $message): void
    {
        session()->flash('flash_notification', ['level' => $level, 'message' => $message]);
    }

    public function success(string $message): void
    {
        $this->message('success', $message);
    }

    public function error(string $message): void
    {
        $this->message('danger', $message);
    }

    public function info(string $message): void
    {
        $this->message('info', $message);
    }
}
