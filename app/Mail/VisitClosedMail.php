<?php

namespace App\Mail;

use App\Models\Visit;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf; // 👈

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
        // Generar PDF desde la vista
        $pdf = Pdf::loadView('pdf.visit', ['visit' => $this->visit])->output();
        $fileName = 'reporte-visita-'.$this->visit->id.'.pdf';

        return $this->subject('Visita finalizada - '.$this->visit->client->name)
            ->markdown('emails.visits.closed', ['visit' => $this->visit])
            ->attachData($pdf, $fileName, ['mime' => 'application/pdf']);
    }
}
