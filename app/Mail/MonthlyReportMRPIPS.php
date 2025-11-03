<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\PDF;

class MonthlyReportMRPIPS extends Mailable
{
    use Queueable, SerializesModels;

    public $pdf;
    public $month;
    public $year;
    public $data;

    public function __construct(PDF $pdf, $month, $year, $data)
    {
        $this->pdf = $pdf;
        $this->month = $month;
        $this->year = $year;
        $this->data = $data;
    }

    public function build()
    {
        return $this->subject("Raport MRPiPS za {$this->month}/{$this->year}")
            ->view('emails.reports.monthly') // tutaj Twój Blade
            ->with($this->data)
            ->attachData(
                $this->pdf->output(),
                "raport_miesieczny_{$this->year}_{$this->month}.pdf",
                ['mime' => 'application/pdf']
            );
    }
}
