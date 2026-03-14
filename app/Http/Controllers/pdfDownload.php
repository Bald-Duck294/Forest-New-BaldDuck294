<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf; // Ensure you import the correct namespace
use Illuminate\Http\Request;

class PdfDownload extends Controller
{
    public function generatePDF()
    {
        $data = [
            'title' => 'Laravel PDF Example',
            'date' => date('m/d/Y'),
        ];

        // Load the Blade view and pass the data
        $pdf = Pdf::loadView('pdf.example', $data);

        // Return the PDF for download
        return $pdf->download('example.pdf');
    }
}
