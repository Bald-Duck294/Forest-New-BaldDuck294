<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AllGuardAttendance implements FromView, ShouldAutoSize
{
    protected $params;

    // We pass the single $params array in through the controller
    public function __construct(array $params)
    {
        $this->params = $params;
    }

    public function view(): View
    {
        // Point this directly to your existing Excel blade file
        return view('AttendanceReport.allGuardReportWithSite', $this->params);
    }
}
