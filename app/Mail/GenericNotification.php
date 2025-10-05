<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GenericNotification extends Mailable
{
    use Queueable, SerializesModels;

    public array $lines;

    public function __construct(public string $subjectLine, array $lines)
    {
        $this->lines = $lines;
    }

    public function build()
    {
        return $this->subject($this->subjectLine)
            ->markdown('emails.generic', ['lines' => $this->lines]);
    }
}
