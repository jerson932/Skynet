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
        $mail = $this->subject('Visita finalizada - '.$this->visit->client->name)
            ->markdown('emails.visits.closed', ['visit' => $this->visit]);

        // Solo generar PDF en entorno local para evitar errores en Railway
        if (!app()->environment('production')) {
            try {
                $pdf = Pdf::loadView('pdf.visit', ['visit' => $this->visit])->output();
                $fileName = 'reporte-visita-'.$this->visit->id.'.pdf';
                $mail->attachData($pdf, $fileName, ['mime' => 'application/pdf']);
            } catch (\Exception $e) {
                // Si falla la generación de PDF, continuar sin adjunto
                \Log::warning('PDF generation failed: ' . $e->getMessage());
            }
        }

        return $mail;
    }
}
