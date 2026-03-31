<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths; // <-- Added this

class PatrollingStatusExport implements FromView, WithColumnWidths // <-- Replaced ShouldAutoSize
{
    protected $patrols;
    protected $companyName;
    protected $reportMonth;
    protected $patrolSubType;
    protected $allData;
    protected $clientId;
    protected $beatId;
    protected $employeeId;

    public function __construct($patrols, $companyName, $reportMonth, $patrolSubType, $allData, $clientId, $beatId, $employeeId)
    {
        $this->patrols = $patrols;
        $this->companyName = $companyName;
        $this->reportMonth = $reportMonth;
        $this->patrolSubType = $patrolSubType;
        $this->allData = $allData;
        $this->clientId = $clientId;
        $this->beatId = $beatId;
        $this->employeeId = $employeeId;
    }

    public function view(): View
    {
        return view('exports.patrolling_status', [
            'patrols'       => $this->patrols,
            'companyName'   => $this->companyName,
            'reportMonth'   => $this->reportMonth,
            'patrolSubType' => $this->patrolSubType,
            'allData'       => $this->allData,
            'clientId'      => $this->clientId,
            'beatId'        => $this->beatId,
            'employeeId'    => $this->employeeId,
        ]);
    }

    // <-- This perfectly sizes your Excel columns! -->
    public function columnWidths(): array
    {
        return [
            'A' => 8,   // Sr. No.
            'B' => 25,  // User Name
            'C' => 25,  // Range (Client)
            'D' => 25,  // Beat Name (Site)
            'E' => 15,  // Type
            'F' => 15,  // Session
            'G' => 20,  // Start Time
            'H' => 20,  // End Time
            'I' => 30,  // Start Location
            'J' => 30,  // End Location
            'K' => 15,  // Distance
            'L' => 15,  // Status
        ];
    }
}
