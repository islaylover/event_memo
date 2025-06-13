<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Domain\Dto\AlertNotificationDto;


class EventReminderMail extends Mailable
{
    use Queueable, SerializesModels;
    public AlertNotificationDto $dto;

    public function __construct(AlertNotificationDto $dto)
    {
        $this->dto = $dto;
    }


    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('【リマインダー】イベント「'.$this->dto->eventName.'」')
            ->view('emails.event_reminder')
            ->with(['dto' => $this->dto]);
    }
}
