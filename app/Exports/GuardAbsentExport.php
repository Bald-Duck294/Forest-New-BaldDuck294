<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class GuardAbsentExport implements FromView, ShouldAutoSize
{
    protected $groupedData;
    protected $subType;
    protected $site;
    protected $startDate;
    protected $endDate;
    protected $guardType;
    protected $daysCount;
    protected $type;
    protected $flag;
    protected $client;
    protected $companyName;

    public function __construct($groupedData, $subType, $site, $startDate, $endDate, $guardType, $daysCount, $type, $flag, $client, $companyName)
    {
        $this->groupedData = $groupedData;
        $this->subType = $subType;
        $this->site = $site;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->guardType = $guardType;
        $this->daysCount = $daysCount;
        $this->type = $type;
        $this->flag = $flag;
        $this->client = $client;
        $this->companyName = $companyName;
    }

    public function view(): View
    {
        $user = session('user');

        return view('reports.workingSummaryReport', [
            'groupedData'      => $this->groupedData,
            'subType'          => $this->subType,
            'geofences'        => $this->site,      // Matches your 'all' check
            'startDate'        => $this->startDate,
            'endDate'          => $this->endDate,
            'daysCount'        => $this->daysCount,
            'companyName'      => $this->companyName, // Ensure this is the object
            'client'           => $this->client,
            'clientName'       => $this->client instanceof \App\ClientDetails ? $this->client->name : 'All Clients',
            'siteName'         => $this->site == 'all' ? 'All Sites' : ($this->siteName ?? $this->site),
            'generatedOn'      => date("d M Y , h:i a"),
            'date'             => $this->startDate . ' to ' . $this->endDate,
            'fileType'         => 'xlsx',
            'user'             => $user,
            'attendanceSubType' => $this->subType,
        ]);
    }
}
