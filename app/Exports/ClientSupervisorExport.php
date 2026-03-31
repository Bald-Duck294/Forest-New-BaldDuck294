<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ClientSupervisorExport implements FromView, ShouldAutoSize
{
    // Define properties to hold the data
    protected $data, $weekoffs, $names, $attendCount, $date, $daysCount, $startDatee;
    protected $companyName, $startDate, $endDate, $currentDate, $companyId, $dateFormat;
    protected $geofences, $flag, $actualTimeformat, $gpsTimeformat, $subType, $sites;
    protected $clients, $supervisorSites, $generatedOn, $attendanceSubType;

    public function __construct(
        $data, $weekoffs, $names, $attendCount, $date, $daysCount, $startDatee,
        $companyName, $startDate, $endDate, $currentDate, $companyId, $dateFormat,
        $geofences, $flag, $actualTimeformat, $gpsTimeformat, $subType, $sites,
        $clients, $supervisorSites, $generatedOn, $attendanceSubType = null
    ) {
        $this->data = $data;
        $this->weekoffs = $weekoffs;
        $this->names = $names;
        $this->attendCount = $attendCount;
        $this->date = $date;
        $this->daysCount = $daysCount;
        $this->startDatee = $startDatee;
        $this->companyName = $companyName;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->currentDate = $currentDate;
        $this->companyId = $companyId;
        $this->dateFormat = $dateFormat; // Storing the variable
        $this->geofences = $geofences;
        $this->flag = $flag;
        $this->actualTimeformat = $actualTimeformat;
        $this->gpsTimeformat = $gpsTimeformat;
        $this->subType = $subType;
        $this->sites = $sites;
        $this->clients = $clients;
        $this->supervisorSites = $supervisorSites;
        $this->generatedOn = $generatedOn;
        $this->attendanceSubType = $attendanceSubType;
    }

    public function view(): View
    {
        return view('reports.clientSupervisorReport', [
            'data'              => $this->data,
            'weekoffs'          => $this->weekoffs,
            'names'             => $this->names,
            'attendCount'       => $this->attendCount,
            'date'              => $this->date,
            'daysCount'         => $this->daysCount,
            'startDatee'        => $this->startDatee,
            'companyName'       => $this->companyName,
            'fromdate'          => $this->startDate,
            'todate'            => $this->endDate,
            'dateFormat'        => $this->dateFormat,
            'flag'              => $this->flag,
            'generatedOn'       => $this->generatedOn ?? $this->currentDate,
            'attendanceSubType' => $this->attendanceSubType,
            'subType'           => $this->subType,
            'client'            => $this->clients,
            'sites'             => $this->sites,
            'supervisorSites'   => $this->supervisorSites,
            'actualTimeformat'  => $this->actualTimeformat,
            'gpsTimeformat'     => $this->gpsTimeformat,
        ]);
    }
}
