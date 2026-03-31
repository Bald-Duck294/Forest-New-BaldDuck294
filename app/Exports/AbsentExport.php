<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
// 1. IMPORT THIS:
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

// 2. ADD "ShouldAutoSize" TO THIS LINE:
class AbsentExport implements FromView, ShouldAutoSize
{
    protected $data;
    protected $companyName;
    protected $reportMonth;
    protected $flag;
    protected $subType;
    protected $generatedOn;

    public function __construct($data, $companyName, $reportMonth, $flag, $subType, $generatedOn)
    {
        $this->data = $data;
        $this->companyName = $companyName;
        $this->reportMonth = $reportMonth;
        $this->flag = $flag;
        $this->subType = $subType;
        $this->generatedOn = $generatedOn;
    }

    public function view(): View
    {
        return view('AttendanceReport.absentReport', [
            'data' => $this->data,
            'companyName' => $this->companyName,
            'dateRange' => $this->reportMonth,
            'flag' => $this->flag,
            'subType' => $this->subType,
            'generatedOn' => $this->generatedOn,
        ]);
    }
}
