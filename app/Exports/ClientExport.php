<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClientExport implements FromCollection, WithHeadings
{
    protected $clients;
    protected $companyName;

    public function __construct($clients, $companyName = '')
    {
        $this->clients = $clients;
        $this->companyName = $companyName;
    }

    public function collection()
    {
        return $this->clients->map(function($client, $index) {
            return [
                'sr_no' => $index + 1,
                'name' => $client->name,
                'address' => $client->address,
                'state' => $client->state,
                'city' => $client->city,
                'pincode' => $client->pincode,
                'contact_person' => $client->spokesperson,
                'contact' => $client->contact,
                'email' => $client->email,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Sr. No.',
            'Client Name',
            'Address',
            'State',
            'City',
            'Pincode',
            'Contact Person',
            'Contact Number',
            'Email'
        ];
    }
}
