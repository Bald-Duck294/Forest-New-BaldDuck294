<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ForgotToMarkExitExport implements FromView, ShouldAutoSize
{
    protected $data;
    protected $subType;
    protected $siteName;
    protected $startDate;
    protected $endDate;
    protected $companyName;

    public function __construct($data, $subType, $siteName, $startDate, $endDate, $companyName)
    {
        $this->data = $data;
        $this->subType = $subType;
        $this->siteName = $siteName;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->companyName = $companyName;
    }

    public function view(): View
    {
        return view('reports.forgotToMarkExitExcel', [
            'data'        => $this->data,
            'subType'     => $this->subType,
            'siteName'    => $this->siteName,
            // Ensure this variable exists for the "All site" check in Blade
            'geofences'   => ($this->siteName == 'All Sites' || $this->siteName == 'all') ? 'all' : $this->siteName,
            'startDate'   => $this->startDate,
            'endDate'     => $this->endDate,
            'companyName' => $this->companyName,
            'generatedOn' => date("d M Y, h:i a")
        ]);
    }
}
