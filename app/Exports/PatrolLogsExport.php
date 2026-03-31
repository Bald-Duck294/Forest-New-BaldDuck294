<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PatrolLogsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $logs;
    protected $companyName;
    protected $dateRange;
    protected $logType;

    public function __construct($logs, $companyName, $dateRange, $logType)
    {
        $this->logs = $logs;
        $this->companyName = $companyName;
        $this->dateRange = $dateRange;
        $this->logType = $logType;
    }

    public function collection()
    {
        return $this->logs;
    }

    public function headings(): array
    {
        return [
            ['Organization:', $this->companyName, 'Date Range:', $this->dateRange, 'Type:', ucfirst($this->logType)], // Report Meta Row
            [], // Empty Row for spacing
            [
                '#',
                'Log Type',
                'Notes',
                'Latitude',
                'Longitude',
                'Created By',
                'Range',
                'Beat',
                'Created At',
                'Photos (URLs)',
                'Additional Payload Data'
            ]
        ];
    }

    public function map($log): array
    {
        static $index = 1;

        // Process Payload into a readable string
        $payloadData = '';
        if (isset($log['payload']) && is_array($log['payload'])) {
            foreach ($log['payload'] as $key => $value) {
                if (in_array($key, ['createdAt', 'created_at'])) continue;

                $label = ucfirst(str_replace(['_', '-'], ' ', preg_replace('/([a-z])([A-Z])/', '$1 $2', $key)));
                $val = is_bool($value) ? ($value ? 'Yes' : 'No') : (is_array($value) ? json_encode($value) : $value);
                $payloadData .= "{$label}: {$val} | ";
            }
        }

        // Process Media URLs
        $photos = '';
        if (isset($log['media']) && is_array($log['media'])) {
            foreach ($log['media'] as $m) {
                $photos .= "https://fms.pugarch.in/public/storage/" . $m['path'] . "\n";
            }
        }

        return [
            $index++,
            ucfirst(str_replace('_', ' ', $log['type'])),
            $log['notes'] ?? '-',
            $log['lat'],
            $log['lng'],
            $log['session']['user']['name'] ?? 'N/A',
            $log['session']['site']['client_name'] ?? 'N/A',
            $log['session']['site']['name'] ?? 'N/A',
            date('d-m-Y h:i A', strtotime($log['created_at'])),
            trim($photos),
            trim($payloadData, ' | ')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the Report Info row
            1 => ['font' => ['bold' => true, 'size' => 12]],
            // Style the Table Headers (Row 3)
            3 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '343A40']
                ]
            ],
        ];
    }
}
