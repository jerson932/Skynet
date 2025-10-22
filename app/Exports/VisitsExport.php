<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Collection;

class VisitsExport
{
    protected Collection $visits;

    public function __construct(Collection $visits)
    {
        $this->visits = $visits;
    }

    public function toXlsxStream()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['ID','Cliente','Técnico','Supervisor','Programada','Check-in','Check-out','Estado','Notas'];
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($this->visits as $v) {
            $status = $v->check_out_at ? 'completada' : ($v->check_in_at ? 'en curso' : 'pendiente');
            $sheet->setCellValue("A{$row}", $v->id);
            $sheet->setCellValue("B{$row}", $v->client->name ?? '');
            $sheet->setCellValue("C{$row}", optional($v->tecnico)->name ?? '');
            $sheet->setCellValue("D{$row}", optional($v->supervisor)->name ?? '');
            $sheet->setCellValue("E{$row}", $v->scheduled_at);
            $sheet->setCellValue("F{$row}", $v->check_in_at);
            $sheet->setCellValue("G{$row}", $v->check_out_at);
            $sheet->setCellValue("H{$row}", $status);
            $sheet->setCellValue("I{$row}", $v->notes ?? '');
            $row++;
        }

        $writer = new Xlsx($spreadsheet);

        // return a stream callback that writes XLSX to output
        return function() use ($writer) {
            $writer->save('php://output');
        };
    }
}
