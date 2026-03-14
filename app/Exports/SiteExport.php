<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SiteExport implements FromCollection, WithHeadings
{
    protected $sites;
    protected $companyName;
    protected $clientName;

    public function __construct($sites, $companyName = '', $clientName = '')
    {
        $this->sites = $sites;
        $this->companyName = $companyName;
        $this->clientName = $clientName;
    }

    public function collection()
    {
        return $this->sites->map(function($site, $index) {
            return [
                'sr_no' => $index + 1,
                'name' => $site->name,
                'client_name' => $site->client_name,
                'address' => $site->address,
                'state' => $site->state,
                'city' => $site->city,
                'contact_person' => $site->contactPerson,
                'mobile' => $site->mobile,
                'email' => $site->email,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Sr. No.',
            'Site Name',
            'Client Name',
            'Address',
            'State',
            'City',
            'Contact Person',
            'Mobile',
            'Email'
        ];
    }
}
