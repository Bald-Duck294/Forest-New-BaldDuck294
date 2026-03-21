<?php

namespace App\Exports;

use App\IncidenceDetails;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\View\View;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IncidentExport implements FromView, ShouldAutoSize, WithEvents, WithStyles
{
    private $IncidenceDetails;
    private $geofences;
    private $month;
    private $fromDate;
    private $toDate;
    private $incidenceSubType;
    private $client;
    private $siteName;
    public function __construct($IncidenceDetails, $geofences, $month, $fromDate, $toDate, $incidenceSubType, $client, $siteName)
    {
        $this->IncidenceDetails = $IncidenceDetails;
        $this->geofences = $geofences;
        $this->month = $month;
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->incidenceSubType = $incidenceSubType;
        $this->client = $client;
        $this->siteName = $siteName;
    }

    /**
     * @return View
     */
    public function view(): View
    {
        return view('reports.incidenceReport', [
            'IncidenceDetails' => $this->IncidenceDetails,
            'geofences' => $this->geofences,
            'month' => $this->month,
            'fromDate' => $this->fromDate,
            'toDate' => $this->toDate,
            'client' => $this->client,
            'siteName' => $this->siteName
        ]);
    }

    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {

            $cellRange = 'A1:M1'; // All headers
            $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(14);
            $event->sheet->getActiveSheet()->setTitle('Name of Sheet 1');
        },
        ];
    }
    public function styles(Worksheet $sheet)
    {

        $sheet->setShowGridlines(false);
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->setTitle("incidence_Export");
        $sheet->getStyle('A1:M1')->getAlignment()->setWrapText(true);
    // $sheet->getStyle('A1:M1'); $sheet->setBreak( 'A10' ,\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::BREAK_ROW);



    }
}
