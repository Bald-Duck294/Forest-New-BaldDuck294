<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class OnSiteExport implements FromView, ShouldAutoSize
{
    protected $data, $geofencesNew, $reportMonth, $startDate, $endDate, $companyName;
    protected $geofences, $clientName, $siteName, $extraParam, $generatedOn;

    public function __construct(
        $data,
        $geofencesNew,
        $reportMonth,
        $startDate,
        $endDate,
        $companyName,
        $geofences,
        $clientName,
        $siteName,
        $extraParam,
        $generatedOn
    ) {
        $this->data = $data;
        $this->geofencesNew = $geofencesNew;
        $this->reportMonth = $reportMonth;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->companyName = $companyName;
        $this->geofences = $geofences;
        $this->clientName = $clientName;
        $this->siteName = $siteName;
        $this->extraParam = $extraParam;
        $this->generatedOn = $generatedOn;
    }

    public function view(): View
    {
        return view('AttendanceReport.onSiteAttendanceReport', [
            'data'         => $this->data,
            'reportMonth'  => $this->reportMonth,
            'dateRange'    => $this->reportMonth, // <--- FIXED: Added this to match Blade
            'startDate'    => $this->startDate,
            'endDate'      => $this->endDate,
            'companyName'  => $this->companyName,
            'geofences'    => $this->geofencesNew,
            'clientName'   => $this->clientName,
            'siteName'     => $this->siteName,
            'generatedOn'  => $this->generatedOn,
            'user'         => session('user'),    // <--- FIXED: Added this for role checks
        ]);
    }
}
