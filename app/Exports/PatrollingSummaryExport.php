<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Illuminate\Support\Collection;

class PatrollingSummaryExport implements FromView, WithColumnWidths
{
    protected $summary;
    protected $companyName;
    protected $dateRange;

    public function __construct(Collection $summary, $companyName, $dateRange)
    {
        $this->summary = $summary;
        $this->companyName = $companyName;
        $this->dateRange = $dateRange;
    }

    public function view(): View
    {
        return view('exports.patrolling_summary', [
            'summary'     => $this->summary,
            'companyName' => $this->companyName,
            'dateRange'   => $this->dateRange,
        ]);
    }

    // This explicitly sets the width of Excel columns A through H
    public function columnWidths(): array
    {
        return [
            'A' => 25, // Employee
            'B' => 15, // Range
            'C' => 15, // Beat
            'D' => 18, // Total Sessions
            'E' => 15, // Completed
            'F' => 15, // Ongoing
            'G' => 22, // Total Distance (km)
            'H' => 22, // Avg Distance (km)
        ];
    }
}
