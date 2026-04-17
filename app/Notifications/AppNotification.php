<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AppNotification extends Notification
{
    use Queueable;

    /**
     * @param  string  $title  Short notification title
     * @param  string  $body   Secondary detail line
     * @param  string  $type   'info' | 'warning' | 'danger' | 'success'
     * @param  string|null  $url   Optional click-through URL
     */
    public function __construct(
        public readonly string  $title,
        public readonly string  $body,
        public readonly string  $type = 'info',
        public readonly ?string $url  = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body'  => $this->body,
            'type'  => $this->type,
            'url'   => $this->url,
        ];
    }
}
