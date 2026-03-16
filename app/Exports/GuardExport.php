<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GuardExport implements FromCollection, WithHeadings, WithMapping
{
    protected $guards;
    protected $absent;
    protected $late;
    protected $companyName;
    protected $type;

    public function __construct($guards, $absent, $late, $companyName, $type)
    {
        // For the export, we combine the relevant collections based on what you want to output.
        // If it's the unassigned export, $guards contains the unassigned users.
        $this->guards = $guards;
    }

    public function collection()
    {
        return $this->guards;
    }

    public function map($guard): array
    {
        // Map role ID to name
        $role = 'Guard';
        if ($guard->role_id == 2)
            $role = 'Admin';
        if ($guard->role_id == 3)
            $role = 'Supervisor';

        return [
            $guard->name ?? $guard->user_name ?? 'N/A',
            $role,
            $guard->contact ?? 'N/A',
            $guard->site_name ?? 'Unassigned',
            $guard->shift_name ?? 'N/A',
        ];
    }

    public function headings(): array
    {
        return [
            'Employee Name',
            'Role',
            'Contact',
            'Assigned Site',
            'Shift',
        ];
    }
}