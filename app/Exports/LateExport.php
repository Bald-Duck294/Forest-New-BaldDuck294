<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LateExport implements FromView, ShouldAutoSize
{
    // 1. Define all properties matching your Controller's call
    protected $data, $companyName, $reportMonth, $flag, $subType, $client, $clientName, $siteName, $generatedOn;

    // 2. Update constructor to accept all 9 arguments
    public function __construct($data, $companyName, $reportMonth, $flag, $subType, $client, $clientName, $siteName, $generatedOn)
    {
        $this->data = $data;
        $this->companyName = $companyName;
        $this->reportMonth = $reportMonth;
        $this->flag = $flag;
        $this->subType = $subType;
        $this->client = $client;
        $this->clientName = $clientName;
        $this->siteName = $siteName;
        $this->generatedOn = $generatedOn;
    }

    public function view(): View
    {
        // 3. Pass ALL variables to the Blade view
        return view('AttendanceReport.lateReport', [
            'data' => $this->data,
            'companyName' => $this->companyName,
            'dateRange' => $this->reportMonth,
            'flag' => $this->flag,
            'subType' => $this->subType,
            'client' => $this->client,
            'clientName' => $this->clientName ?? 'N/A',
            'siteName' => $this->siteName ?? 'N/A',
            'generatedOn' => $this->generatedOn,
        ]);
    }
}
