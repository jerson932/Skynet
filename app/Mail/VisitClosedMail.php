<?php

namespace App\Mail;

use App\Models\Visit;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VisitClosedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Visit $visit;

    public function __construct(Visit $visit)
    {
        $this->visit = $visit->load(['client','supervisor','tecnico']);
    }

    public function build()
    {
        return $this->subject('Visita finalizada - '.$this->visit->client->name)
                    ->markdown('emails.visits.closed', [
                        'visit' => $this->visit,
                    ]);
    }
}
