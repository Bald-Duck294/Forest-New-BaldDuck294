<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class IncidenceSummaryExport implements FromView, ShouldAutoSize
{
    protected $data;
    protected $geofences;
    protected $startDate;
    protected $endDate;
    protected $daysCount;
    protected $type;
    protected $client;

    // Must match the 7 variables sent from ReportController line 380
    public function __construct($data, $geofences, $startDate, $endDate, $daysCount, $type, $client)
    {
        $this->data = $data;
        $this->geofences = $geofences;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->daysCount = $daysCount;
        $this->type = $type;
        $this->client = $client;
    }

    public function view(): View
    {
        // Pointing to your incidence summary view
        return view('reports.incidenceSummaryReport', [
            'IncidenceDetails' => $this->data, // <--- THIS IS THE LINE THAT CHANGED
            'geofences' => $this->geofences,
            'fromDate' => $this->startDate,
            'toDate' => $this->endDate,
            'daysCount' => $this->daysCount,
            'type' => $this->type,
            'client' => $this->client,

            // Adding a few fallbacks based on what your Blade file usually asks for!
            'total' => is_countable($this->data) ? count($this->data) : 0,
            'geoName' => $this->geofences,
            'incidenceSubType' => 'incidenceSummaryReport',
        ]);
    }
}
