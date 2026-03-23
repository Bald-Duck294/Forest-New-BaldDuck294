<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$categories = DB::table('forest_reports')->select('category')->distinct()->pluck('category');
$types = DB::table('forest_reports')->select('report_type')->distinct()->pluck('report_type');
$companies = DB::table('forest_reports')->select('company_id')->distinct()->pluck('company_id');

echo "Categories: " . implode(', ', $categories->toArray()) . "\n";
echo "Types: " . implode(', ', $types->toArray()) . "\n";
echo "Companies: " . implode(', ', $companies->toArray()) . "\n";
echo "Total Records: " . DB::table('forest_reports')->count() . "\n";
