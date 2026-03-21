<?php

namespace App\Http\Controllers;

// namespace Carbon\Carbon;
// use Dompdf\Dompdf;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use DateTime;
use DateTimeZone;
use App\Exports\AllGuardAttendance;
use App\Exports\ClientWiseAttendance;
use App\Exports\OnSiteExport;
use App\Exports\IncidentExport;
use App\Exports\TourSummaryExport;
use App\Exports\VisitorExport;
use App\Exports\TourExport;
use App\Exports\GuardMonthlyExport;
use App\Exports\SiteWiseGuardExport;
use App\Exports\IncidenceSummaryExport;
use App\Exports\ClientSupervisorExport;
use App\Exports\GuardAbsentExport;
use App\Exports\ForgotToMarkExitExport;
use App\Exports\AbsentExport;
use App\Exports\LateExport;
use App\Exports\ClientVisitExport;
use App\Exports\TourDiaryExport;
use App\Exports\PerformanceExport;
use App\Exports\PatrollingStatusExport;
use App\Exports\PatrollingSummaryExport;
use App\Exports\PatrolLogsExport;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Http\Request;
use App\ClientDetails;
use App\User;

use App\CompanyDetails;
use Maatwebsite\Excel\Excel;
use App\Exports\View;
use App\Exports\VisitorExportCount;
use App\GuardTour;
use App\SiteDetails;
use App\Attendance;
use App\GuardTourLog;
use App\ActivityLog;
use App\ClientVisit;
use App\GetDays;
use App\SiteAssign;
use App\PatrolSession;
use App\PatrolLog;

use App\Models\TourDiary;
use App\Users;
use App\Leaves;
use App\Exports\GuardAttendanceExport;
use Log;
use Maatwebsite\Excel\Concerns\ToArray;
// use PDF;
use Session;

class ReportController extends Controller
{
    private $excel;
    private $generatedOn;
    public function __construct(Excel $excel)
    {
        $this->excel = $excel;
        $this->generatedOn = now()->format('d F Y , h:i:a');
    }

    public function calculateWeekOffDates(array $weekOffDays, DateTime $fromDate, DateTime $toDate)
    {
        $weekOffDates = [];
        foreach ($weekOffDays as $day) {
            $currentDate = clone $fromDate;
            while ($currentDate <= $toDate) {
                if ($currentDate->format('l') === $day) {
                    $weekOffDates[] = $currentDate->format('Y-m-d');
                }
                $currentDate->modify('+1 day');
            }
        }
        return $weekOffDates;
    }


    // download daily tour report
    public function downloadDailyTour(Request $request)
    {
        $user = session('user');
        ActivityLog::create([
            'date_time' => date('Y-m-d H:i:s'),
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Tour Report Download",
            'message' => "Tour report downloaded by " . $user->name,
        ]);
        $subtype = $request->subtype;
        if ($subtype == 'DailyTour') {
            $tourDate = $request->tourDate;
            $cur_date = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
            $date = $cur_date->format('d-m-Y');
            //dd($date);
            $userId = $request->userId;
            $GuardTourLog = DB::table('guard_tour');
            if ($userId == 'all') {
                $GuardTourLog = $GuardTourLog->where('site_id', $request->geofences);
                $geo = SiteDetails::where('id', $request->geofences)->first();
            } else {
                $attendance = Attendance::where('user_id', $request->userId)
                    ->where('dateFormat', $tourDate)
                    ->first();
                if (isset($attendance)) {
                    $guardTour = GuardTourLog::where('guardId', $request->userId)
                        ->where('date', $tourDate)->select('tourId')->distinct()
                        ->get()->toArray();
                    $geo = SiteDetails::where('id', $request->geofences)->first();
                    $GuardTourLog = $GuardTourLog->whereIn('id', $guardTour);
                } else {
                    return redirect()->back()->with('alert', 'Records not found');
                }
            }
            $type = "dailyTour";
            $GuardTourLog = $GuardTourLog->get();
            if ($request->xlsx == "xlsx") {
                return $this->excel->download(new TourExport($GuardTourLog, $tourDate, $date, $userId, $geo, $type), 'Daily-Tour-Report.xlsx');
            } else {
                // return $this->excel->download(new TourExport($GuardTourLog, $tourDate, $date, $userId, $geo, $type), 'guard_tour_report.pdf');
                $pdf = PDF::loadView('reports.tourDaily', ['GuardTourLog' => $GuardTourLog, 'tourDate' => $tourDate, 'date' => $date, 'userId' => $userId, 'geo' => $geo, 'type' => $type])->setPaper('a4', 'portrait');
                return $pdf->stream('Daily-Tour-Report.pdf');
                // return $pdf->download('Daily-Tour-Report.pdf');
            }
        } else {
            $type = "summaryView";
            $startDate = $request->startDate;
            $endDate = $request->endDate;
            $userId = $request->userId;
            $GuardTourLog = DB::table('guard_tour');
            // if ($userId == 'all') {
            $GuardTourLog = $GuardTourLog->where('site_id', $request->geofences);
            $geofence = SiteDetails::where('id', $request->geofences)->first();
            $geo = $geofence->id;

            $datetime1 = new DateTime($startDate);
            $datetime2 = new DateTime($endDate);
            $interval = $datetime1->diff($datetime2);
            $daysCount = (int) $interval->format('%a');
            $daysCount = $daysCount + 1;
            // } else {
            // $attendance = Attendance::where('user_id', $userId)
            //     ->whereBetween('dateFormat', [$startDate, $endDate])
            //     ->first();
            // if (isset($attendance)) {
            //     $guardTour = GuardTourLog::where('guardId', $userId)
            //         ->whereBetween('tourDate', [$startDate, $endDate])->select('tourId')->distinct()
            //         ->get()->toArray();
            // $geo = SiteDetails::where('id', $request->geofences)->first();
            // $GuardTourLog = $GuardTourLog->where('site_id', $request->geofences);
            // } else {
            //     return redirect()->back()->with('alert', 'Records not found');
            // }
            // }
            $GuardTourLog = $GuardTourLog->get();
            if ($request->xlsx == "xlsx") {
                return $this->excel->download(new TourSummaryExport($GuardTourLog, $startDate, $endDate, $userId, $geo, $daysCount, $type), 'Tour-Summary-Report.xlsx');
            } else {
                $pdf = PDF::loadView('reports.tourSummary', ['GuardTourLog' => $GuardTourLog, 'startDate' => $startDate, 'endDate' => $endDate, 'userId' => $userId, 'geo' => $geo, 'daysCount' => $daysCount])->setPaper('a4', 'landscape');
                return $pdf->stream('Tour-Summary-Report.pdf');
                //return $pdf->download('Tour-Summary-Report.pdf');
                //return $this->excel->download(new TourSummaryExport($GuardTourLog, $startDate, $endDate, $userId, $geo, $daysCount, $type), 'tour_summary_report.pdf');
            }
        }
    }



    // incidence report
    public function IncidenceReport($fromDate, $toDate, $geofences, $priority, $incidenceSubType)
    {
        $endDate = date('Y-m-d', strtotime($toDate));
        $startDate = date('Y-m-d', strtotime($fromDate));
        $datetime1 = new DateTime($startDate);
        $datetime2 = new DateTime($endDate);
        // dump($datetime1 , $datetime2);
        $interval = $datetime1->diff($datetime2);
        $daysCount = (int) $interval->format('%a');
        $daysCount = $daysCount + 1;
        $reportMonth = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));
        $geoName = DB::table('incidence_details')->where('site_id', '=', $geofences)->first();

        if ($incidenceSubType == 'incidenceReport') {
            $IncidenceDetails = DB::table('incidence_details')->where('site_id', '=', $geofences);
            if ($priority == 'All') {
                $IncidenceDetails = $IncidenceDetails->whereBetween('dateFormat', [$startDate, $endDate]);
            } else {
                $IncidenceDetails = $IncidenceDetails->whereBetween('dateFormat', [$startDate, $endDate])->where('priority', $priority);
            }
            $IncidenceDetails = $IncidenceDetails->get();
            if (count($IncidenceDetails) > 0) {
                return view('reports/incidenceReportView')->with('IncidenceDetails', $IncidenceDetails)->with('geoName', $geoName)->with('geofences', $geofences)->with('toDate', $endDate)->with('fromDate', $startDate)->with('priority', $priority)->with('incidenceSubType', $incidenceSubType);
            } else {
                return redirect()->back()->with('alert', 'Records not found'); //redirect()->route('report.view');
            }
        } else {

            $IncidenceDetails = DB::table('incidence_details')->where('site_id', '=', $geofences)->whereBetween('dateFormat', [$startDate, $endDate]);
            $IncidenceDetails = $IncidenceDetails->get();
            if (count($IncidenceDetails) > 0) {
                return view('reports/incidenceSummaryReportView')->with('IncidenceDetails', $IncidenceDetails)->with('geoName', $geoName)->with('geofences', $geofences)->with('incidenceSubType', $incidenceSubType)->with('toDate', $endDate)->with('daysCount', $daysCount)->with('fromDate', $startDate);
            } else {
                return redirect()->back()->with('alert', 'Records not found'); //redirect()->route('report.view');
            }
        }
    }

    // download incidence report
    public function downloadIncidenceReport(Request $request)
    {
        // dd($request->all() , "all request data");
        $user = session('user');
        ActivityLog::create([
            'date_time' => date('Y-m-d H:i:s'),
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Incidence Report Download",
            'message' => "Incidence report downloaded by " . $user->name,
        ]);
        $client = $request->client;
        $geoName = $request->geofences;
        $month = $request->month;
        $fromDate = $request->fromDate;
        $toDate = $request->toDate;
        // dump($toDate, $fromDate);
        $geofences = $request->geofences;
        $priority = $request->priority;
        $siteName = $request->siteName;
        $incidenceSubType = $request->incidenceSubType;
        // $IncidenceDetails = DB::table('incidence_details')->whereBetween('dateFormat', [$fromDate, $toDate]);
        // dump('main', $IncidenceDetails->pluck('priority')->toArray());

        //         dd([
        //     'geoName' => $geoName,
        //     'toDate' => $toDate,
        //     'fromDate' => $fromDate,
        //     'priority' => $priority,
        //     'incidenceSubType' => $incidenceSubType
        // ]);
        // dd('above incidence report');

        $endDate = date('Y-m-d', strtotime($request->toDate));
        $startDate = date('Y-m-d', strtotime($request->fromDate));

        // dump($client , $geofences  ,"data about client and geofences");
        if ($client == 'all' || $client != 'all' && $geofences == 'all') {
            $siteName = 'All';
            // dump($companyName , "companyName");

        } else {
            $site = SiteAssign::where('site_id', $geofences)->first();
            $siteName = $site->site_name;
            // dump($siteName , $geofences , $companyName , "company data");

        }

        if ($client == "all") {
            $IncidenceDetails = DB::table('incidence_details as inci')
                // ->join('site_details as site', 'site.id', '=', 'inci.site_id')
                ->where('inci.company_id', $user->company_id)
                ->whereBetween('inci.dateFormat', [$startDate, $endDate]);
        } elseif ($geofences == 'all') {
            // dd($request->client,$startDate,$endDate);
            $IncidenceDetails = DB::table('incidence_details as inci')
                // ->join('site_details as site', 'site.id', '=', 'inci.site_id')
                ->where('inci.client_id', $request->client)
                ->whereBetween('inci.dateFormat', [$startDate, $endDate]);
            //->where('inci.site_id', '=', $geofences)
            // ->whereBetween('dateFormat', [$startDate, $endDate])->get();

        } else {
            $IncidenceDetails = DB::table('incidence_details')->where('site_id', '=', $geofences);
        }


        if (isset($request->toDate) && isset($request->fromDate)) {


            if ($priority == 'All') {

                $IncidenceDetails = $IncidenceDetails;
                // dd($startDate, $endDate, $IncidenceDetails);
            } else {

                $IncidenceDetails = $IncidenceDetails->where('inci.priority', $priority);
            }
        }



        $IncidenceDetails = $IncidenceDetails->get();
        // dd('above incidence', $IncidenceDetails);

        // dump('Final filtered data before export:', $IncidenceDetails->pluck('id')->toArray());

        // if ($client != 'all' && $geofences != 'all') {
        //     $IncidenceDetails = $IncidenceDetails->get();
        // }

        if ($incidenceSubType == 'incidenceReport') {
            // dump('Initial request data:', $request->all()); // Dump the request data
            // dd('inside incidence report');

            // dd($siteName,"Incidence Details289");
            // dd('below incidence');
            if ($request->xlsx == 'xlsx') {
                // dump('Exporting to Excel');
                return $this->excel->download(new IncidentExport(
                    $IncidenceDetails,
                    $geoName,
                    $month ?? null,
                    $fromDate ?? null,
                    $toDate ?? null,
                    $incidenceSubType,
                    $client,
                    $siteName ?? null
                ), 'Incidence Report.xlsx');
            } else {
                // dd('Exporting to PDF',$request->siteName);
                $pdf = PDF::loadView('reports.incidenceReport', [
                    'IncidenceDetails' => $IncidenceDetails,
                    'geoName' => $geoName,
                    'month' => $month ?? null,
                    'fromDate' => $fromDate ?? null,
                    'toDate' => $toDate ?? null,
                    'incidenceSubType' => $incidenceSubType,
                    'client' => $client,
                    'siteName' => $request->siteName
                ])->setPaper('a4', 'landscape');

                return $pdf->stream('Incidence Report.pdf');
            }
        } else {
            // dd('IN incidence Summary report', $IncidenceDetails);

            // if (isset($request->geofences)) {
            //     $IncidenceDetails = $IncidenceDetails->where('site_id', '=', $request->geofences);
            // } else {
            //     return redirect()->back()->with('alert', 'Site not selected');
            // }

            $endDate = $request->toDate;
            $startDate = $request->fromDate;
            $datetime1 = new DateTime($startDate);
            $datetime2 = new DateTime($endDate);
            // dump($datetime1 , $datetime2);
            $interval = $datetime1->diff($datetime2);
            $daysCount = (int) $interval->format('%a');
            $daysCount = $daysCount + 1;
            $type = "Incidence Summary Report";
            $reportMonth = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));

            // $IncidenceDetails = $IncidenceDetails->whereBetween('dateFormat', [$startDate, $endDate])->where('priority', $priority);
            // dump($IncidenceDetails->pluck('id')->toArray(),"Incidence Details xc1");
            // $IncidenceDetails = $IncidenceDetails->get();
            // dd($IncidenceDetails,"Incidence Details xc");
            // dump('helo');
            // dd([
            //     'IncidenceDetails' => $IncidenceDetails,
            //     'geofences' => $request->geofences,
            //     'startDate' => $startDate,
            //     'endDate' => $endDate,
            //     'daysCount' => $daysCount,
            //     'type' => $type
            // ]);
            // dd($type);
            if ($request->xlsx == "xlsx") {
                return $this->excel->download(new IncidenceSummaryExport(
                    $IncidenceDetails,
                    $request->geofences,
                    $startDate,
                    $endDate,
                    $daysCount,
                    $type,
                    $client
                ), 'Incidence-Summary-Report.xlsx');
            } else {
                // dd($IncidenceDetails,"Incidence Details xc3");
                // return $this->excel->download(new IncidenceSummaryExport($IncidenceDetails, $geoName, $startDate, $endDate, $daysCount, $type), 'incidence_summary_report.pdf');
                $pdf = PDF::loadView('reports.incidenceSummaryReport', ['IncidenceDetails' => $IncidenceDetails, 'geofences' => $request->geofences, 'client' => $client, 'fromDate' => $fromDate, 'toDate' => $toDate, 'daysCount' => $daysCount])->setPaper('a4', 'landscape');

                return $pdf->stream('Incidence-Summary-Report.pdf');
                // return $pdf->download('Incidence-Summary-Report.pdf');
            }
        }
    }

    // incidence summary report
    public function incidenceSummary($date, $type, $geofences)
    {
        $dateFormat = date('Y-m-d', strtotime($date));
        $IncidenceDetails = DB::table('incidence_details')->where('dateformat', $dateFormat)->where('type', $type)->where('site_id', $geofences)->get();
        // dd($IncidenceDetails);
        $modaldata = view('IncidenceReport/incidenceSummaryView')->with('IncidenceDetails', $IncidenceDetails)->with('geofences', $geofences)->with('dateFormat', $dateFormat)->with('type', $type)->render();
        echo $modaldata;
    }

    // download incidence summary report
    public function downloadIncidenceSummary(Request $request)
    {
        $user = session('user');
        if ($user) {
            $client = $request->client;
            $geoName = $request->geofences;
            $month = $request->month;
            $fromDate = $request->fromDate;
            $toDate = $request->toDate;
            // dump($toDate, $fromDate);
            $geofences = $request->geofences;
            $priority = $request->priority;
            $siteName = $request->siteName;
            $incidenceSubType = $request->incidenceSubType;

            $endDate = date('Y-m-d', strtotime($request->toDate));
            $startDate = date('Y-m-d', strtotime($request->fromDate));
            ActivityLog::create([
                'date_time' => date('Y-m-d H:i:s'),
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'type' => "Incidence Summary Report",
                'message' => "Incidence summary report downloaded by " . $user->name,
            ]);
            $type = str_replace('-', ' ', $request->type);
            $dateFormat = date('Y-m-d', strtotime($request->dateFormat));
            // DB::enableQueryLog();
            // $IncidenceDetails = DB::table('incidence_details')->where('dateformat', $dateFormat)->where('type', $type)->where('site_id', $request->geofences)->get();
            // dd(DB::getQueryLog());

            if ($client == "all") {
                $IncidenceDetails = DB::table('incidence_details as inci')
                    // ->join('site_details as site', 'site.id', '=', 'inci.site_id')
                    ->where('inci.company_id', $user->company_id)
                    ->whereBetween('inci.dateFormat', [$startDate, $endDate]);
            } elseif ($geofences == 'all') {
                // dd($request->client,$startDate,$endDate);
                $IncidenceDetails = DB::table('incidence_details as inci')
                    // ->join('site_details as site', 'site.id', '=', 'inci.site_id')
                    ->where('inci.client_id', $request->client)
                    ->whereBetween('inci.dateFormat', [$startDate, $endDate]);
                //->where('inci.site_id', '=', $geofences)
                // ->whereBetween('dateFormat', [$startDate, $endDate])->get();

            } else {
                $IncidenceDetails = DB::table('incidence_details')->where('site_id', '=', $geofences);
            }
            // $geo = SiteDetails::where('id', $request->geofences)->first();
            // dd($IncidenceDetails);
            // $geoName = $geo->name;
            $startDate = $request->dateFormat;
            $endDate = 0;
            $daysCount = 0;
            $type = "summaryView";
            if ($request->xlsx == 'xlsx') {

                return $this->excel->download(
                    new IncidenceSummaryExport(
                        $IncidenceDetails,
                        $geoName,
                        $startDate,
                        $endDate,
                        $daysCount,
                        $type
                    ),
                    'Incidence-Summary-Report.xlsx'
                );
            } else {
                $pdf = PDF::loadView('IncidenceReport.incidenceSummary', ['IncidenceDetails' => $IncidenceDetails, 'geoName' => $geoName, 'startDate' => $startDate, 'endDate' => $endDate, 'daysCount' => $daysCount, 'type' => $type])->setPaper('a4', 'landscape');
                return $pdf->stream('Incidence-Summary-Report.pdf');
                // return $pdf->download('Incidence-Summary-Report.pdf');
                // return $this->excel->download(new IncidenceSummaryExport($IncidenceDetails, $geoName, $startDate, $endDate, $daysCount, $type), 'incidence_summary_report.pdf');
            }
        }
    }

    // visitor report
    public function VisitorReport($fromDate, $toDate, $geofences, $visitorSubType)
    {
        $startDate = date('Y-m-d', strtotime($fromDate));
        $endDate = date('Y-m-d', strtotime($toDate));
        $visitorSubType = $visitorSubType;
        $VisitorDetails = DB::table('visitor_details');
        if ($visitorSubType == 'visitorReport') {
            $VisitorDetails = $VisitorDetails->where('site_id', '=', $geofences)->whereBetween('dateFormat', [$startDate, $endDate]);
            $VisitorDetails = $VisitorDetails->get();
            if (count($VisitorDetails) > 0) {
                return view('reports/visitorDailyView')->with('VisitorDetails', $VisitorDetails)->with('geofences', $geofences)->with('fromDate', $startDate)->with('toDate', $endDate);
            }
        } else {
            $VisitorDetails = $VisitorDetails->where('site_id', '=', $geofences)->whereBetween('dateFormat', [$startDate, $endDate]);
            $VisitorDetails = $VisitorDetails->get();
            // dd($VisitorDetails);
            if (count($VisitorDetails) > 0) {
                return view('reports/visitorReportCount')->with('VisitorDetails', $VisitorDetails)->with('geofences', $geofences)->with('fromDate', $startDate)->with('toDate', $endDate);
            }
        }
    }

    // download visitor report
    public function downloadVisitorReport(Request $request)
    {
        $user = session('user');
        ActivityLog::create([
            'date_time' => date('Y-m-d H:i:s'),
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Visitor Report",
            'message' => "Visitor report downloaded by " . $user->name,
        ]);
        $type = "simple";
        $VisitorDetails = DB::table('visitor_details');
        if (isset($request->geofences)) {
            $VisitorDetails = $VisitorDetails->where('site_id', '=', $request->geofences)->whereBetween('dateFormat', [$request->fromDate, $request->toDate]);
            $VisitorDetails = $VisitorDetails->get();
            if (count($VisitorDetails) > 0) {
                if ($request->xlsx == 'xlsx') {
                    return $this->excel->download(new VisitorExport($VisitorDetails, $type), 'Visitor-Report.xlsx');
                } else {
                    $pdf = PDF::loadView('reports.visitorDaily', ['VisitorDetails' => $VisitorDetails])->setPaper('a4', 'landscape');
                    return $pdf->stream('Visitor-Report.pdf');
                    // return $pdf->download('Visitor-Report.pdf');
                    // return $this->excel->download(new VisitorExport($VisitorDetails, $type), 'visitor_report.pdf');
                }
            } else {
                return redirect()->back()->with('alert', 'Records not found');
            }
        } else {
            return redirect()->back()->with('alert', 'Records not found');
        }
    }

    // download visitor report count
    public function downloadVisitorReportCount(Request $request)
    {
        $user = session('user');
        if ($user) {
            ActivityLog::create([
                'date_time' => date('Y-m-d H:i:s'),
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'type' => "Visitor Report Count",
                'message' => "Visitor report downloaded by " . $user->name,
            ]);
            $geofences = $request->geofences;
            $fromDate = $request->fromDate;
            $toDate = $request->toDate;
            $VisitorDetails = DB::table('visitor_details');
            if (isset($geofences)) {
                $VisitorDetails = $VisitorDetails->where('site_id', '=', $geofences)->whereBetween('dateFormat', [$request->fromDate, $request->toDate]);
                $VisitorDetails = $VisitorDetails->get()->unique('date');
                if (count($VisitorDetails) > 0) {
                    if ($request->xlsx == "xlsx") {
                        return $this->excel->download(new VisitorExportCount($VisitorDetails, $geofences, $fromDate, $toDate), 'Visitor-Summary-Report.xlsx');
                    } else {
                        $pdf = PDF::loadView('reports.downloadVisitorReportCount', ['VisitorDetails' => $VisitorDetails, 'geofences' => $geofences, 'fromDate' => $fromDate, 'toDate' => $toDate])->setPaper('a4', 'landscape');
                        return $pdf->stream('Visitor-Summary-Report.pdf');
                        // return $pdf->download('Visitor-Summary-Report.pdf');
                        // return $this->excel->download(new VisitorExportCount($VisitorDetails, $geofences, $fromDate, $toDate), 'visitor_report_count.pdf');
                    }
                } else {
                    return redirect()->back()->with('alert', 'Records not found');
                }
            } else {
                return redirect()->back()->with('alert', 'Records not found');
            }
        }
    }

    // visitor summary report
    public function visitorSummaryReport($date, $siteId)
    {
        $dateFormat = date('Y-m-d', strtotime($date));
        $VisitorDetails = DB::table('visitor_details')->where('dateFormat', $dateFormat)->where('site_id', $siteId)->get();
        $modaldata = view('reports/visitorSummaryView')->with('VisitorDetails', $VisitorDetails)->with('date', $date)->with('siteId', $siteId)->render();
        echo $modaldata;
    }

    // download visitor summary report
    public function downloadVisitorSummaryReport(Request $request)
    {
        $VisitorDetails = DB::table('visitor_details');
        $type = "summary";
        $dateFormat = date('Y-m-d', strtotime($request->date));
        $user = session('user');
        ActivityLog::create([
            'date_time' => date('Y-m-d H:i:s'),
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Visitor Summary Report",
            'message' => "Visitor summary report downloaded by " . $user->name,
        ]);
        if (isset($request->siteId)) {
            $VisitorDetails = $VisitorDetails->where('site_id', '=', $request->siteId)->where('dateFormat', $dateFormat);
            $VisitorDetails = $VisitorDetails->get();
            if (count($VisitorDetails) > 0) {
                if ($request->xlsx == "xlsx") {
                    return $this->excel->download(new VisitorExport($VisitorDetails, $type), 'Visitor-Summary-Report.xlsx');
                } else {
                    $pdf = PDF::loadView('reports.visitorSummary', ['VisitorDetails' => $VisitorDetails])->setPaper('a4', 'landscape');
                    return $pdf->stream('Visitor-Summary-Report.pdf');
                    // return $pdf->download('Visitor-Summary-Report.pdf');
                    // return $this->excel->download(new VisitorExport($VisitorDetails, $type), 'visitor_summary_report.pdf');
                }
            } else {
                return redirect()->back()->with('alert', 'Records not found');
            }
        } else {
            return redirect()->back()->with('alert', 'Records not found');
        }
    }

    // download guard attendance report
    public function downloadUserAttendanceReport(Request $request)
    {

        // dump('in  user attendace report');
        $user = session('user');
        ActivityLog::create([
            'date_time' => date('Y-m-d H:i:s'),
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "User Attendance Report",
            'message' => "User attendance report downloaded by " . $user->name,
        ]);
        $startDate = date('Y-m-d', strtotime($request->fromDate));
        $endDate = date('Y-m-d', strtotime($request->toDate));
        //   dd($startDate , $endDate , "all dates");
        $datetime1 = new DateTime($startDate);
        $datetime2 = new DateTime($endDate);
        $interval = $datetime1->diff($datetime2);
        $daysCount = (int) $interval->format('%a');
        $daysCount = $daysCount + 1;
        $reportMonth = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));

        $site_UserIds = [];


        // $site_UserIds = SiteAssign::where('company_id', $user->company_id)->where('user_id', $request->supervisorId)->where('role_id', 2)->get()->pluck('user_id')->unique();

        $test = Users::where('users.company_id', $user->company_id)
            ->where('users.id', $request->supervisorId)
            ->rightjoin('attendance', function ($join) use ($startDate, $endDate) {
                $join->on('users.id', '=', 'attendance.user_id')
                    ->whereBetween('attendance.dateFormat', [$startDate, $endDate]);
            })
            ->select('users.name as name', 'users.id as user_id', 'attendance.dateFormat as date', 'attendance.site_name as site_name', 'attendance.duration_for_calc as duration', 'attendance.entry_time', 'attendance.exit_date_time', 'attendance.gpsTime')
            ->orderBy('name');


        //   $data = Attendance::where('user_id', $request->supervisorId)
        //       ->whereBetween('dateFormat', [$startDate, $endDate])
        //       ->get()->groupBy('dateFormat');

        // dump($test->pluck('user_id')->toArray() , [$startDate, $endDate] , $site_UserIds , "test");
        $data = $test->groupBy(['user_id', 'name', 'date', 'site_name'])
            ->get()
            ->mapToGroups(function ($item) {
                // Create an array with all the required information
                $attendanceInfo = [
                    'date' => $item['date'],
                    'site_name' => $item['site_name'],
                    'entry_time' => $item['entry_time'] ?? null,
                    'exit_date_time' => $item['exit_date_time'] ?? null,
                    'time_difference' => $item['duration'] ?? null,
                    'gpsTime' => $item['gpsTime'] ?? null
                ];

                // Return with user_id as key and attendance info as value
                return [$item['user_id'] => $attendanceInfo];
            });
        // dd($data , $request->supervisorId ,'data');
        $attendance = Attendance::where('user_id', $request->supervisorId)->whereBetween('dateFormat', [$startDate, $endDate])
            ->selectRaw('sum(TIME_TO_SEC(time_calculation)) as actualTime, sum(TIME_TO_SEC(gpsTime)) as gpsTime')->first();

        $actualTime = $attendance->actualTime;
        $gpsTime = $attendance->gpsTime;

        $hours = floor($actualTime / 3600);
        $mins = floor(($actualTime / 60) % 60);

        $gpshours = floor($gpsTime / 3600);
        $gpsmins = floor(($gpsTime / 60) % 60);

        $actualTimeformat = sprintf('%02dhr %02dmin', $hours, $mins);
        $gpsTimeformat = sprintf('%02dhr %02dmin', $gpshours, $gpsmins);
        $company = CompanyDetails::where('id', $user->company_id)->first();
        $companyName = $company->name;
        $datePresent = Attendance::where('user_id', $request->supervisorId)
            ->whereBetween('dateFormat', [$startDate, $endDate])
            ->select('dateFormat')->distinct()->pluck('dateFormat')->toArray();
        $userinfo = SiteAssign::where('user_id', $request->supervisorId)->first();
        $days = json_decode($userinfo->weekoff);
        $weekOffDates = [];


        //Object Monday is  not the actual logic just defined here to stop making the read lines in controller
        //   $monday = (object)[
        //       'day' => 'monday',
        //       'format' => 'none'
        //   ]; //Object Monday is   not the actual logic just defined here to stop making the read lines in controller

        if ($days && count($days) > 0) {
            foreach ($days as $key => $value) {
                $utility = new GetDays();
                $mondays = $utility->getDays($value, $datetime1->format('F'), $datetime1->format('Y'), $datetime2->format('F'), $datetime2->format('Y'));
                if (gettype($monday) != 'string') {
                    $monday = $monday->format('Y-m-d');
                }
                foreach ($mondays as $index => $monday) {
                    $weekOffDates[] = date('d-m-Y', strtotime($monday));
                }
                $lastSundayDate = date('d-m-Y', strtotime('+7 days ' . $weekOffDates[count($weekOffDates) - 1]));
                $lastSunday = new DateTime($lastSundayDate, new DateTimeZone('Asia/Kolkata'));
                if ($lastSunday < $datetime2) {
                    $weekOffDates[] = $lastSundayDate;
                }
            }
        }

        // if ($days && count($days) > 0) {
        //     foreach ($days as $key => $value) {
        //         $utility = new GetDays();
        //         $mondays = $utility->getDays($value, $datetime1->format('F'), $datetime1->format('Y'), $datetime2->format('F'), $datetime2->format('Y'));
        //         foreach ($mondays as $index => $monday) {
        //             $weekOffDates[] = date('d-m-Y', strtotime($monday));
        //         }
        //         $lastSundayDate = date('d-m-Y', strtotime('+7 days ' . $weekOffDates[count($weekOffDates) - 1]));
        //         $lastSunday = new DateTime($lastSundayDate, new DateTimeZone('Asia/Kolkata'));
        //         if ($lastSunday < $datetime2) {
        //             $weekOffDates[] = $lastSundayDate;
        //         }
        //     }
        // }
        $supervisorId = $request->supervisorId;
        $flag = $request->xlsx;

        // dd($data , "data");  
        $subType = "Supervisor Attendance Report";

        $variables = [
            'subType' => $subType,
            'companyName' => $companyName,
            'fromDate' => $startDate,
            'toDate' => $endDate,
            'daysCount' => $daysCount,
            'data' => $data,
            'datePresent' => $datePresent,
            'weekOffDates' => $weekOffDates,
            'actualTimeformat' => $actualTimeformat,
            'gpsTimeformat' => $gpsTimeformat,
            'supervisorId' => $request->supervisorId,
            'geofences' => $request->geofences,
            'guardId' => $request->supervisorId,
            'guardName' => $user,

        ];

        // Dump all variables at once
        // dump($variables);
        // dd($data);
        // if (count($data) > 0) {
        if ($request->xlsx == "xlsx") {
            // dd($request);
            return $this->excel->download(new GuardMonthlyExport($companyName, $startDate, $endDate, $daysCount, $data, $datePresent, $weekOffDates, $actualTimeformat, $gpsTimeformat, $supervisorId, $flag, $subType), 'Supervisor-Attendance-Report.xlsx');
        } else {
            $pdf = PDF::loadView('reports.userMonthlyReport', ['companyName' => $companyName, 'startDate' => $startDate, 'endDate' => $endDate, 'daysCount' => $daysCount, 'data' => $data, 'datePresent' => $datePresent, 'weekOffDates' => $weekOffDates, 'actualTimeformat' => $actualTimeformat, 'gpsTimeformat' => $gpsTimeformat, 'supervisorId' => $supervisorId, 'flag' => $flag, 'subType' => $subType])->setPaper('a4', 'portrait');
            return $pdf->stream('Supervisor_Visit_Report.pdf');
            // return $pdf->download('Supervisor-Attendance-Report.pdf');
            // return $this->excel->download(new GuardMonthlyExport($companyName, $startDate, $endDate, $daysCount, $data, $datePresent, $weekOffDates, $actualTimeformat, $gpsTimeformat), 'supervisor_report.pdf');
        }
        // } else {
        //     return redirect()->back()->with('alert', 'Records not found');
        // }
    }



    public function downloadClientWiseReport(Request $request)
    {
        // dump(1);
        $user = session('user');
        $startDate = date('Y-m-d', strtotime($request->fromdate));
        $endDate = date('Y-m-d', strtotime($request->todate));
        $fileType = $request->xlsx;

        // Calculate days difference
        $datetime1 = new DateTime($startDate);
        $datetime2 = new DateTime($endDate);
        $interval = $datetime1->diff($datetime2);
        $daysCount = (int) $interval->format('%a') + 1;

        // Set date formats
        $dateFormat = "Range";
        $date = date('d M Y', strtotime($startDate)) . " to " . date('d M Y', strtotime($endDate));
        $startDatee = date('d-m-Y', strtotime($startDate));
        $currentDate = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
        $currentDate = $currentDate->format("d-m-Y");

        // Set report type
        if ($request->attendanceSubType == 'EmployeeAttendanceReportwithHours') {
            $subType = 'Employee Attendance Report With Hours';
        } else if ($request->attendanceSubType == 'EmployeeAttendanceReport') {
            $subType = 'Employee Attendance Report';
        } else {
            $subType = $request->subType;
        }

        $site = SiteAssign::where('user_id', $user->id)->first();

        if ($user->role_id == 2) {
            $siteArray = json_decode($site['site_id'], true);
            $site_UserIds = SiteAssign::where('company_id', $user->company_id)
                ->whereIn('site_id', $siteArray)
                ->where('role_id', 3)
                ->get()
                ->pluck('user_id')
                ->unique();
        } else {
            $site_UserIds = SiteAssign::where('client_id', $request->client)
                ->get()
                ->pluck('user_id')
                ->unique();
        }

        // Get attendance data based on user role
        if ($user->role_id == 2) {
            $test = Users::where('users.company_id', $user->company_id)
                ->rightjoin('attendance', 'users.id', '=', 'attendance.user_id')
                ->rightjoin('site_assign', 'users.id', '=', 'site_assign.user_id')
                ->whereIn('users.id', $site_UserIds)
                ->whereBetween('attendance.dateFormat', [$startDate, $endDate])
                ->select(
                    'users.name as name',
                    'users.id as user_id',
                    'attendance.date as date',
                    'attendance.site_name as site_name',
                    'attendance.time_difference as duration',
                    'site_assign.client_name as client_name'
                )
                ->orderBy('name');
        } else {
            $test = Users::where('users.company_id', $user->company_id)
                ->leftjoin('attendance', 'users.id', '=', 'attendance.user_id')
                ->leftjoin('site_assign', 'users.id', '=', 'site_assign.user_id')
                ->whereIn('users.id', $site_UserIds)
                ->whereBetween('attendance.dateFormat', [$startDate, $endDate])
                ->select(
                    'users.name as name',
                    'users.id as user_id',
                    'attendance.date as date',
                    'attendance.site_name as site_name',
                    'attendance.time_difference as duration',
                    'site_assign.client_name as client_name'
                )
                ->orderBy('name');
        }

        $allData = $test->get();

        // Process data using collection methods
        $groupedData = $allData->groupBy('user_id');
        $names = [];
        $hours = [];
        $data = [];
        foreach ($groupedData as $userId => $group) {
            $names[$userId] = $group->pluck('name')->unique()->values()->toArray();
            $hours[$userId] = $group->unique('date')->values()->pluck('duration')->toArray();
            $data[$userId] = $group->pluck('date')->unique()->values()->toArray();
        }


        // Fetch site data consistently
        $sites = $this->fetchSiteData($user, $request, $site_UserIds, $request->geofences, $request->client);

        // Get user IDs
        $userIds = array_keys($names);


        // dd($data, 'data');
        // Get weekoffs data
        $weekoffData = SiteAssign::whereIn('user_id', $userIds)->groupBy(['user_id', 'weekoff']);
        $weekoffs = $weekoffData->get()->mapToGroups(function ($item, $key) {
            return [$item['user_id'] => $item['weekoff']];
        })->toArray();

        // Handle missing users
        //   $additionalUsers = SiteAssign::where('client_id', $request->client)
        //     ->where('company_id', $user->company_id)
        //     ->where('role_id', 3)
        //     ->whereNotIn('user_id', $userIds)
        //     ->select('user_id', 'user_name as name')
        //     ->get();

        // // Add these users to the data structure
        // foreach ($additionalUsers as $user) {
        //     $data[$user->user_id] = [];
        //     $names[$user->user_id] = [$user->name];
        // }
        // dump($data, 'prev');

        $users = Users::where('users.company_id', $user->company_id)
            ->rightjoin('site_assign', 'users.id', '=', 'site_assign.user_id')
            ->where('users.showUser', 1)
            ->whereNotIn('users.id', $userIds)

            ->when($user->role_id == 2, fn($query) => $query->whereNotIn('users.role_id', [1, 2, 4])->whereIn('users.id', $site_UserIds))
            ->when($user->role_id == 1, fn($query) => $query->whereIn('users.role_id', [2, 3]))
            ->when($user->role_id == 7, fn($query) => $query->whereIn('users.id', $site_UserIds))->whereIn('users.id', $site_UserIds)
            ->orderBy('site_assign.client_name')
            ->orderBy('site_assign.site_name')
            ->select('users.id', 'users.name')
            ->get();


        foreach ($users as $key => $value) {
            //$data[$value['id']] = [];
            $data[$value['id']] = [];
            $names[$value['id']] = [$value['name']];
        }
        // dump($data, 'prev');

        // dump($data, 'after');

        // Get attendance counts
        $attendance = Attendance::where('company_id', $user->company_id)
            ->whereBetween('dateFormat', [$startDate, $endDate])
            ->whereIn('user_id', $site_UserIds)
            ->whereIn('role_id', [2, 3]);

        $attendCount = $attendance->groupBy(['user_id', 'dateFormat'])
            ->get()->mapToGroups(function ($item, $key) {
                return [$item['dateFormat'] => $item['user_id']];
            })->toArray();

        ksort($attendCount);

        // Get company and client details
        $clientName = ClientDetails::where('id', $request->client)->first();
        $companyData = CompanyDetails::where('id', $user->company_id)->first();
        $companyName = $companyData->name;

        if (count($data) > 0) {
            // Log activity
            ActivityLog::create([
                'date_time' => date('Y-m-d H:i:s'),
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'type' => "Client Wise Report",
                'message' => "Client wise report downloaded by " . $user->name,
            ]);

            if ($request->xlsx == "xlsx") {
                return $this->excel->download(new ClientWiseAttendance(
                    $data,
                    $clientName,
                    $weekoffs,
                    $names,
                    $attendCount,
                    $date,
                    $daysCount,
                    $startDatee,
                    $companyName,
                    $request->fromdate,
                    $request->todate,
                    $currentDate,
                    $dateFormat,
                    $sites,
                    $hours,
                    $subType,
                    $request->attendanceSubType,
                    $fileType,
                    $this->generatedOn
                ), $subType . '.xlsx');
            } else {
                if ($startDate == $endDate)
                    $customPaper = "A4";
                else
                    $customPaper = array(0, 0, 1909, 2700);
                $pdf = PDF::loadView('AttendanceReport.clientWiseReport', [
                    'data' => $data,
                    'weekoffs' => $weekoffs,
                    'names' => $names,
                    'sites' => $sites,
                    'hours' => $hours,
                    'attendCount' => $attendCount,
                    'date' => $date,
                    'daysCount' => $daysCount,
                    'startDatee' => $startDatee,
                    'companyName' => $companyName,
                    'clientName' => $clientName,
                    'currentDate' => $currentDate,
                    'subType' => $subType,
                    'attendanceSubType' => $request->attendanceSubType,
                    'type' => 'pdf',
                    'fileType' => $fileType,
                    'generatedOn' => $this->generatedOn
                ])->setPaper($customPaper, 'landscape');

                return $pdf->stream($subType);
            }
        } else {
            return redirect()->back()->with('alert', 'Records not found');
        }
    }


    // download site wise guard attendance report
    public function downloadSiteWiseGuardReport(Request $request)
    {
        $user = session('user');
        $startDate = $request->fromDate;
        $endDate = $request->toDate;
        $geofences = $request->geofences;
        $guard = $request->guard;
        $type = $request->type;
        $client = $request->client;

        // Calculate days count
        $datetime1 = new DateTime($startDate);
        $datetime2 = new DateTime($endDate);
        $interval = $datetime1->diff($datetime2);
        $daysCount = (int) $interval->format('%a') + 1;

        // Determine subType based on attendanceSubType using if-else
        if ($request->attendanceSubType == 'EmployeeAttendanceReportwithHours') {
            $subType = 'Employee Attendance Report With Hours';
        } elseif ($request->attendanceSubType == 'EmployeeAttendanceReport') {
            $subType = 'Employee Attendance Report';
        } elseif ($request->attendanceSubType == 'Employee') {
            $subType = 'Employee Attendance Report';
        } elseif ($request->attendanceSubType == 'Site') {
            $subType = 'Employee Attendance Report With Site';
        } else {
            $subType = 'Employee Attendance Report With Site';
        }

        // Log activity
        ActivityLog::create([
            'date_time' => date('Y-m-d H:i:s'),
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Attendance Report SiteWise",
            'message' => "Sitewise attendance report downloaded by " . $user->name,
        ]);

        if ($user->role_id == 2) {
            // $siteArray = json_decode($site['site_id'], true);
            $siteUserIds = SiteAssign::where('company_id', $user->company_id)
                ->where('site_id', $geofences)
                ->where('role_id', 3)
                ->get()
                ->pluck('user_id')
                ->unique();
        } else {
            $siteUserIds = SiteAssign::where('client_id', $request->client)->where('site_id', $geofences)
                ->pluck('user_id')
                ->unique();
        }


        // dump( $siteUserIds , $client , $request->client, $geofences , "siteuser ids");
        // Base query for attendance data with hours
        $test = Users::where('users.company_id', $user->company_id)
            ->rightJoin('attendance', 'users.id', '=', 'attendance.user_id')
            ->rightJoin('site_assign', 'users.id', '=', 'site_assign.user_id')
            ->whereIn('users.id', $siteUserIds)
            ->whereBetween('attendance.dateFormat', [$startDate, $endDate])
            ->select(
                'users.name as name',
                'users.id as user_id',
                'attendance.date as date',
                'attendance.site_name as site_name',
                'attendance.time_difference as duration',
                'site_assign.client_name as client_name'
            )
            ->orderBy('name');

        // Main user data query with role-based filtering
        $userData = SiteAssign::where('site_assign.company_id', $user->company_id)
            ->leftJoin('attendance', 'site_assign.user_id', '=', 'attendance.user_id')
            ->when($user->role_id == 2, function ($query) use ($user, $geofences) {
                $siteIds = SiteAssign::where('user_id', $user->id)
                    ->pluck('site_id')
                    ->map(function ($item) {
                        return is_string($item) ? json_decode($item, true) : $item;
                    })
                    ->flatten()
                    ->unique()
                    ->values();

                if ($geofences != 'all') {
                    if (!$siteIds->contains($geofences)) {
                        return response()->json(['error' => 'You are not authorized to access this site'], 403);
                    }
                    return $query->where('site_assign.site_id', $geofences);
                }
                return $query->whereIn('site_assign.site_id', $siteIds);
            })
            ->when($user->role_id != 2 && $geofences != 'all', function ($query) use ($geofences) {
                return $query->where('site_assign.site_id', $geofences);
            })
            ->whereBetween('attendance.dateFormat', [$startDate, $endDate])
            ->select(
                'site_assign.user_name as name',
                'site_assign.user_id as user_id',
                'attendance.date as date'
            )
            ->distinct()
            ->orderBy('site_assign.user_name', 'ASC');

        // Process user data
        $allUserData = $userData->get();
        // Process user data robustly
        $groupedUserData = $allUserData->groupBy('user_id');
        $names = [];
        $data = [];
        foreach ($groupedUserData as $userId => $group) {
            $names[$userId] = $group->pluck('name')->unique()->values()->toArray();
            $data[$userId] = $group->pluck('date')->unique()->values()->toArray();
        }
        $userIds = array_keys($names);

        // Get sites data if needed
        $sites = [];
        $allAttendanceData = collect();
        if ($request->attendanceSubType == 'EmployeeAttendanceReportwithSite') {
            $allAttendanceData = $test->get();
            $sites = $allAttendanceData->mapToGroups(function ($item) {
                return [
                    $item['user_id'] => [
                        'site' => $item['site_name'],
                        'client' => $item['client_name']
                    ]
                ];
            })->toArray();
        }

        // Determine site name
        if ($geofences == 'all' || $geofences === null) {
            $siteName = 'All Sites';
        } else {
            $siteName = SiteAssign::where('site_id', $geofences)->value('site_name');
        }

        // Get weekoffs
        $weekoffs = SiteAssign::whereIn('user_id', $userIds)
            ->groupBy(['user_id', 'weekoff'])
            ->get()
            ->mapToGroups(function ($item) {
                return [$item['user_id'] => $item['weekoff']];
            })
            ->toArray();

        // Add missing users
        $siteData = SiteAssign::where('site_id', $geofences)
            ->whereNotIn('user_id', $userIds)
            ->get();

        foreach ($siteData as $value) {
            $weekoffs[$value['user_id']] = $value['weekoff'] ? [$value['weekoff']] : [];
            $data[$value['user_id']] = [];
            $names[$value['user_id']] = [$value['user_name']];
        }

        // Get sorted user IDs
        $users = Users::whereIn('id', array_keys($data))
            ->orderBy('name', 'ASC')
            ->pluck('id')
            ->toArray();

        // Get attendance counts
        $attendCount = Attendance::whereIn('user_id', $userIds)
            ->whereBetween('dateFormat', [$startDate, $endDate])
            ->groupBy(['user_id', 'dateFormat'])
            ->get()
            ->mapToGroups(function ($item) {
                return [$item['dateFormat'] => $item['user_id']];
            })
            ->toArray();

        // Sort attendance counts
        ksort($attendCount);

        // Get hours data
        $hours = $test->get()
            ->mapToGroups(function ($item) {
                return [$item['user_id'] => $item['duration']];
            })
            ->toArray();

        // Get company data
        $companyData = CompanyDetails::where('id', $user->company_id)->first();
        $companyName = $companyData->name;
        $companyId = $companyData->id;
        $clientName = $request->clientName;
        // Format dates
        $dateFormat = "Range";
        $date = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));
        $startDatee = date('d-m-Y', strtotime($startDate));
        $currentDate = now()->timezone('Asia/Kolkata')->format("d-m-Y");

        // dump($clientName ,$request->clientName, 'client name');
        // Generate report
        if ($request->xlsx == "xlsx") {
            $flag = "xlsx";
            return $this->excel->download(new SiteWiseGuardExport(
                $users,
                $data,
                $weekoffs,
                $names,
                $attendCount,
                $date,
                $daysCount,
                $startDatee,
                $companyName,
                $startDate,
                $endDate,
                $currentDate,
                $companyId,
                $dateFormat,
                $geofences,
                'xlsx',
                $subType,
                $siteName,
                $sites,
                $request->attendanceSubType,
                $hours,
                $clientName,
                $this->generatedOn
            ), $subType . ".xlsx");
        } else {
            // dump('In site wise');
            $flag = "pdf";
            $customPaper = array(0, 0, 300, 3000);
            $pdf = PDF::loadView('reports.siteWiseGuardReport', [
                'users' => $users,
                'data' => $data,
                'sites' => $sites,
                'weekoffs' => $weekoffs,
                'names' => $names,
                'attendCount' => $attendCount,
                'date' => $date,
                'daysCount' => $daysCount,
                'startDatee' => $startDatee,
                'companyName' => $companyName,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'currentDate' => $currentDate,
                'companyId' => $companyId,
                'dateFormat' => $dateFormat,
                'geofences' => $geofences,
                'flag' => 'pdf',
                'subType' => $subType,
                'siteName' => $siteName,
                'attendanceSubType' => $request->attendanceSubType,
                'hours' => $hours,
                'guard' => $guard,
                'type' => $type,
                'flag' => $flag,
                'clientName' => $clientName,
                'generatedOn' => $this->generatedOn

            ])->setPaper($customPaper, 'landscape');

            return $pdf->stream($subType . ".pdf");
        }
    }



    public function downloadWorkingSummaryReport(Request $request)
    {

        $user = session('user');
        // $startDate = $request->input('startDate');
        // $endDate = $request->input('endDate');
        // dump($startDate , $endDate);
        $site = $request->input('geofences');
        // dump($site , "siteName");
        // $companyName = $request->companyName;
        $companyName = $request->input('companyName');
        // dd($companyName , "name");
        $subType = 'Absent Report';
        $flag = $request->input('flag', 'web');
        // $client=$request->client;
        $clientName = $request->clientName;
        $client = $request->client;
        $geofences = $request->geofences;
        $startDate = $request->startDate;
        $endDate = $request->endDate;
        $datetime1 = new DateTime($startDate);
        $datetime2 = new DateTime($endDate);
        $interval = $datetime1->diff($datetime2);
        $daysCount = (int) $interval->format('%a');
        $daysCount = $daysCount + 1;
        $fileType = $request->xlsx;
        // dump($companyName , $client , "names 414");


        $siteInfo = [
            'name' => 'All Sites',
            'client' => [
                'id' => $client,
                'name' => 'All Clients'
            ]
        ];


        if ($client !== 'all' && $geofences == 'all') {
            $clientDetails = ClientDetails::find($client);
            $siteInfo['client']['name'] = $clientDetails->name ?? 'Unknown Client';
            // dump($siteInfo, "siteInfo");
        } else {
            $clientDetails = ClientDetails::find($client);
            $site = SiteAssign::where('site_id', $geofences)->first();
            // dd($site->site_name , "site");
            $siteInfo['client']['name'] = $clientDetails->name ?? 'All Client';
            $siteInfo['name'] = $site->site_name ?? 'All site';
        }
        // dd($client , $site , "cli data1" );

        $fromDate = new DateTime($startDate);
        $toDate = new DateTime($endDate);

        // Fetch organization and site data
        // $companyName = CompanyDetails::where('id', $user->company_id)->first();
        // $site = SiteDetails::where('id', $site)->first();

        // Fetch guards based on user role
        // Fetch guards based on user role
        // dd($client != 'all' , gettype($client) , $client);


        $guardsQuery = collect();
        if ($user->role_id == 1) {

            $guardsQuery = SiteAssign::query()
                ->where('company_id', $user->company_id)
                ->when($client != 'all' && $geofences == 'all', fn($query) => $query->where('client_id', $client))
                ->when($client != 'all' && $geofences != 'all', fn($query) => $query->where('site_id', $geofences))
                ->orderBy('user_name');
        } else if ($user->role_id == 7) {
            $siteQuery = SiteAssign::where('company_id', $user->company_id);

            // dump('in else if');
            // Fetch site assignments for the user
            $siteAssigned = SiteAssign::where('user_id', $user->id)->first();

            // $client_ids = json_decode($siteAssigned, true);
            $client_ids = json_decode($siteAssigned['site_id'], true);
            // dd($siteAssigned ,  $client_ids,"site Assigned");

            if ($client == 'all') {
                $siteArray = SiteDetails::whereIn('client_id', $client_ids)->pluck('id')->toArray();
                $userIds = SiteAssign::whereIn('client_id', $client_ids)->pluck('user_id')->toArray();
            } else {

                $siteArray = SiteDetails::where('client_id', $client)->pluck('id')->toArray();
                $userIds = SiteAssign::where('client_id', $client)->pluck('user_id')->toArray();
            }


            // dd($siteArray , "array");

            $supervisorIds = Users::where([['users.company_id', '=', $user->company_id], ['users.role_id', '=', 2]])
                ->leftJoin('site_assign as site', 'users.id', '=', 'site.user_id')
                ->where(function ($query) use ($siteArray) {
                    foreach ($siteArray as $siteId) {
                        $query->orWhereRaw('JSON_CONTAINS(site.site_id, ?)', [json_encode([$siteId])]);
                    }
                })->pluck('site.user_id')->toArray();


            // dd($userIds, "user ids");

            $site_UserIds = array_merge($userIds, $supervisorIds);

            // dump($siteQuery->pluck('user_name')->toArray());
            $siteQuery->when(
                $geofences !== 'all' && $geofences !== null,
                fn($query) => $query->where('site_id', $geofences)

            )
                ->whereIn('user_id', $site_UserIds);

            // dd($siteQuery->pluck('client_id')->toArray());
            // dd($siteQuery->pluck('user_id')->toArray());
            // dump('the role id is 7 last');

            $site_UserIds_query = SiteAssign::where('company_id', $user->company_id);
            // dd($site_UserIds);

            if ($client !== 'all' && $geofences == 'all') {
                // dump($client, $geofences, "client");
                // $attendanceQuery->whereIn('user_id', $site_UserIds_query->pluck('user_id'));
                $site_UserIds = $site_UserIds_query->where('client_id', $client)->pluck('user_id');
            } else if ($client !== 'all' && $geofences !== 'all') {
                $site_UserIds = $site_UserIds_query->where('site_id', $geofences)->pluck('user_id');
            }

            $guardsQuery = SiteAssign::query()
                ->where('company_id', $user->company_id)
                ->whereIn('user_id', $site_UserIds)
                ->orderBy('user_name');


            if (!isset($site_UserIds) || count($site_UserIds) === 0) {
                // dd('stop');
                return 'No records found';
            }
        } else {

            // dump('the role id is 2');
            $siteIds = $user->role_id == 2
                ? SiteAssign::where('user_id', $user->id)
                    ->pluck('site_id')
                    ->map(function ($item) {
                        return is_string($item) ? json_decode($item, true) : $item;
                    })
                    ->flatten()
                    ->unique()
                    ->values()
                : collect();
            // dump($siteIds, "siteIds arr");

            $site_UserIds_query = SiteAssign::where('company_id', $user->company_id)->whereIn('site_id', $siteIds);
            // dd($site_UserIds);

            if ($client == 'all') {
                $site_UserIds = $site_UserIds_query->pluck('user_id');
            } else if ($client !== 'all' && $geofences == 'all') {
                // dump($client, $geofences, "client");
                // $attendanceQuery->whereIn('user_id', $site_UserIds_query->pluck('user_id'));
                $site_UserIds = $site_UserIds_query->pluck('user_id');
            } else if ($client !== 'all' && $geofences !== 'all') {
                $site_UserIds = $site_UserIds_query->where('site_id', $geofences)->pluck('user_id');
            }

            $guardsQuery = SiteAssign::query()
                ->where('company_id', $user->company_id)
                ->whereIn('user_id', $site_UserIds)
                ->orderBy('user_name');
        }


        // Fetch guards in bulk
        $guards = $guardsQuery->get();
        // dd( $guards  , "guards");

        // Prepare user IDs and weekoff data
        $guardUserIds = $guards->pluck('user_id')->toArray();
        $guardWeekOffs = $guards->pluck('weekoff', 'user_id')->map(fn($weekoff) => json_decode($weekoff, true) ?? []);

        // Bulk fetch attendance records for guards in the date range
        $attendanceData = Attendance::whereIn('user_id', $guardUserIds)
            ->whereBetween('dateFormat', [$startDate, $endDate])
            ->get()
            ->groupBy('user_id');
        // dd($attendanceData , "attendance data");
        // Calculate attendance and working data
        $totalDays = $fromDate->diff($toDate)->days + 1;
        $groupedData = $guards->map(function ($guard) use ($attendanceData, $guardWeekOffs, $fromDate, $toDate, $totalDays) {
            $userId = $guard->user_id;

            // Calculate weekoff dates for the guard
            $weekOffDays = $guardWeekOffs[$userId] ?? [];
            $weekOffDates = $this->calculateWeekOffDates($weekOffDays, $fromDate, $toDate);

            // Calculate total working days
            $totalWorkingDays = $totalDays - count($weekOffDates);

            // Fetch attendance records for the guard
            // $presentDays = $attendanceData[$userId]->count() ?? 0;
            $presentDays = isset($attendanceData[$userId])
                ? $attendanceData[$userId]->unique('dateFormat')->count()
                : 0;

            // Adjust total working days if attendance exceeds the calculation
            $totalWorkingDays = max($totalWorkingDays, $presentDays);
            $absentDays = $totalWorkingDays - $presentDays;

            return [
                'user_name' => $guard->user_name,
                'totalWorkingDays' => $totalWorkingDays,
                'daysWorked' => $presentDays,
                'absentDays' => $absentDays,
                'weekOffCount' => count($weekOffDates),
            ];
        });

        // Format date for the report
        $dateFormat = config('app.date_format', 'd M Y');

        // Determine site name for the report
        // dd($companyName , $client , "names");
        // dd(DB::getQueryLog());
        $type = "Absent";
        $flag = $request->xlsx;
        // dd($GuardDetails);
        // if (count($GuardDetails) > 0) {
        // dd($guard , "guard");
        // dd($groupedData );

        if ($request->xlsx == 'xlsx') {
            return $this->excel->download(
                new
                    GuardAbsentExport(
                    $groupedData,
                    $subType,
                    $site,
                    $startDate,
                    $endDate,
                    'all guard',
                    $daysCount,
                    $type,
                    $flag,
                    $client,
                    $companyName,
                    $siteInfo['client']['name'],
                    $siteInfo['name'],
                    $geofences,
                    $fileType,
                    $this->generatedOn
                ),
                'working-summary-report.xlsx'
            );
        } else {
            // dd($companyName , $client , "names");

            $pdf = PDF::loadView('reports.workingSummaryReport', [
                'groupedData' => $groupedData,
                'client' => $client,
                'subType' => $subType,
                'geofences' => $site,
                'site' => $site,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'guard' => 'one guard',
                'daysCount' => $daysCount,
                'type' => $type,
                'flag' => $flag,
                'companyName' => $companyName,
                'clientName' => $siteInfo['client']['name'],
                'siteName' => $siteInfo['name'],
                'fileType' => $fileType,
                'generatedOn' => $this->generatedOn


            ])->setPaper('a4', 'landscape');
            return $pdf->stream('Working-Summary-Report.pdf');
            // return $pdf->download('Working-Summary-Report.pdf');
            // return $this->excel->download(new GuardAbsentExport($GuardDetails, $geofences, $startDate, $endDate, $guard, $daysCount, $type), 'guard_apsent_report.pdf');
        }
        // } else {
        //     return redirect()->back()->with('alert', 'Records not found');
        // }
    }

    // single day tour
    public function DayTour($tourDate, $geofences, $tourSubType, $userId)
    {
        $tourDate = date('Y-m-d', strtotime($tourDate));
        $cur_date = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
        $date = $cur_date->format('d-m-Y');
        $subtype = "DailyTour";
        $GuardTourLog = DB::table('guard_tour');
        if ($userId == 'all') {
            $GuardTourLog = $GuardTourLog->where('site_id', $geofences);
            $geo = SiteDetails::where('id', $geofences)->first();
        } else {
            $attendance = Attendance::where('user_id', $userId)
                ->where('dateFormat', $tourDate)
                ->first();
            if (isset($attendance)) {
                $guardTour = GuardTourLog::where('guardId', $userId)
                    ->where('tourDate', $tourDate)->select('tourId')->distinct()
                    ->get()->toArray();
                $geo = SiteDetails::where('id', $attendance->site_id)->first();
                $GuardTourLog = $GuardTourLog->whereIn('id', $guardTour);
            }
        }
        $GuardTourLog = $GuardTourLog->get();
        if (count($GuardTourLog) > 0) {
            return view('reports/tourDailyNew')->with('GuardTourLog', $GuardTourLog)->with('date', $date)->with('userId', $userId)->with('geo', $geo)->with('geofences', $geofences)->with('tourDate', $tourDate)->with('tourDate', $tourDate)->with('subtype', $subtype);
        }
    }

    // tour report
    public function TourReport($fromDate, $toDate, $geofences, $tourSubType, $userId)
    {
        $user = session('user');
        $startDate = date('Y-m-d', strtotime($fromDate));
        $endDate = date('Y-m-d', strtotime($toDate));
        $datetime1 = new DateTime($startDate);
        $datetime2 = new DateTime($endDate);
        $interval = $datetime1->diff($datetime2);
        $daysCount = (int) $interval->format('%a');
        $daysCount = $daysCount + 1;
        if ($tourSubType == 'tourDayWise') {
            $reportMonth = $datetime1->format('d M Y') . " to " . $datetime2->format('d M Y');
            $company = CompanyDetails::where('id', $user->company_id)->first();
            $companyName = $company->name;
            $site = SiteDetails::where('id', $geofences)->first();
            if ($userId == 'all') {
                $data = GuardTourLog::where('site_id', $geofences)->whereBetween('date', [$datetime1->format('Y-m-d'), $datetime2->format('Y-m-d')])->orderBy('date', 'ASC')->orderBy('tourName', 'ASC')->orderBy('round', 'ASC')->get();
                return view('TourReport/tourDayWiseView')->with('data', $data)->with('site', $site)->with('dateRange', $reportMonth)->with('companyName', $companyName)->with('startDate', $startDate)->with('endDate', $endDate)->with('siteId', $geofences);
            } else {
                $data = GuardTourLog::where('guardId', $userId)->whereBetween('date', [$datetime1->format('Y-m-d'), $datetime2->format('Y-m-d')])->orderBy('date', 'ASC')->orderBy('tourName', 'ASC')->orderBy('round', 'ASC')->get();
                return view('TourReport/guardTourReportView')->with('data', $data)->with('site', $site)->with('dateRange', $reportMonth)->with('companyName', $companyName)->with('startDate', $startDate)->with('endDate', $endDate)->with('siteId', $geofences)->with('guard', $userId);
            }
        }
        // else if ($tourSubType == 'guardTourReport') {
        //     $reportMonth = $datetime1->format('d M Y') . " to " . $datetime2->format('d M Y');
        //     $data = GuardTourLog::where('guardId', $userId)->whereBetween('date', [$datetime1->format('Y-m-d'), $datetime2->format('Y-m-d')])->orderBy('date', 'ASC')->orderBy('tourName', 'ASC')->orderBy('round', 'ASC')->get();
        //     $company = CompanyDetails::where('id', $user->company_id)->first();
        //     $companyName = $company->name;
        //     $site = SiteDetails::where('id', $geofences)->first();
        //     // dd($data);
        //     if (count($data) > 0) {
        //         return view('TourReport/guardTourReportView')->with('data', $data)->with('site', $site)->with('dateRange', $reportMonth)->with('companyName', $companyName)->with('startDate', $startDate)->with('endDate', $endDate)->with('siteId', $geofences)->with('guard', $userId);
        //     } else {
        //         return redirect()->back()->with('alert', 'Records not found');
        //     }
        // }
        else {
            $GuardTourLog = DB::table('guard_tour');
            if ($userId == 'all') {
                $GuardTourLog = $GuardTourLog->where('site_id', $geofences);
                $geo = SiteDetails::where('id', $geofences)->first();
            } else {
                $attendance = Attendance::where('user_id', $userId)
                    ->whereBetween('dateFormat', [$startDate, $endDate])
                    ->first();
                if (isset($attendance)) {
                    $guardTour = GuardTourLog::where('guardId', $userId)
                        ->whereBetween('tourDate', [$startDate, $endDate])->select('tourId')->distinct()
                        ->get()->toArray();
                    $geo = SiteDetails::where('id', $attendance->site_id)->first();
                    $GuardTourLog = $GuardTourLog->whereIn('id', $guardTour);
                } else {
                    return redirect()->back()->with('alert', 'Records not found');
                }
            }

            $GuardTourLog = $GuardTourLog->get();
            if (count($GuardTourLog) > 0) {
                return view('reports/tourSummaryView')->with('GuardTourLog', $GuardTourLog)->with('userId', $userId)->with('geo', $geo)->with('geofences', $geofences)->with('startDate', $startDate)->with('endDate', $endDate)->with('daysCount', $daysCount);
            } else {
                return redirect()->back()->with('alert', 'Records not found');
            }
        }
    }

    // tour summary report
    public function tourSummary($date, $tourID, $siteID)
    {
        $tourDate = date('Y-m-d', strtotime($date));
        $GuardTourLog = GuardTourLog::where('tourDate', $tourDate)->where('tourId', $tourID)->get();
        $geofences = SiteDetails::where('id', $siteID)->first();
        $modaldata = view('TourReport/tourSummaryView')->with('GuardTourLog', $GuardTourLog)->with('tourDate', $tourDate)->with('geofences', $geofences)->with('tourID', $tourID)->with('siteID', $siteID)->render();
        echo $modaldata;
    }

    // download tour summary report
    public function downloadTourSummary(Request $request)
    {
        $user = session('user');
        ActivityLog::create([
            'date_time' => date('Y-m-d H:i:s'),
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Tour Summary Report",
            'message' => "Tour summary report downloaded by " . $user->name,
        ]);
        $GuardTourLog = GuardTourLog::where('tourDate', $request->tourDate)->where('tourId', $request->tourID)->get();
        $startDate = $request->tourDate;
        $endDate = 0;
        $userId = 0;
        $daysCount = 0;
        $type = "summary";
        $geo = SiteDetails::where('id', $request->siteID)->first();
        if ($request->xlsx == "xlsx") {
            return $this->excel->download(new TourSummaryExport($GuardTourLog, $startDate, $endDate, $userId, $geo, $daysCount, $type), 'Tour-Summary-Report.xlsx');
        } else {
            $pdf = PDF::loadView('TourReport.tourSummary', ['GuardTourLog' => $GuardTourLog])->setPaper('a4', 'landscape');
            return $pdf->stream('Tour-Summary-Report.pdf');
            // return $pdf->download('Tour-Summary-Report.pdf');
            // return $this->excel->download(new TourSummaryExport($GuardTourLog, $startDate, $endDate, $userId, $geo, $daysCount, $type), 'tour_summary_report.pdf');
        }
    }

    // download tour day wise
    public function downloadTourDayWise(Request $request)
    {
        $user = session('user');


        $startDate = $request->startDate;
        $endDate = $request->endDate;
        $siteId = $request->siteId;
        $datetime1 = new DateTime($startDate);
        $datetime2 = new DateTime($endDate);
        $reportMonth = $datetime1->format('d M Y') . " to " . $datetime2->format('d M Y');

        $GuardTourLog = GuardTourLog::where('site_id', $siteId)->whereBetween('date', [$datetime1->format('Y-m-d'), $datetime2->format('Y-m-d')])->orderBy('date', 'ASC')->orderBy('tourName', 'ASC')->orderBy('round', 'ASC')->get();

        $company = CompanyDetails::where('id', $user->company_id)->first();
        $companyName = $company->name;
        $type = "tourDayWise";
        $site = SiteDetails::where('id', $siteId)->first();
        // dd($GuardTourLog);
        if (count($GuardTourLog) > 0) {
            ActivityLog::create([
                'date_time' => date('Y-m-d H:i:s'),
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'type' => "Tour Day Wise Report",
                'message' => "Tour summary wise report downloaded by " . $user->name,
            ]);
            if ($request->xlsx == "xlsx") {
                return $this->excel->download(new TourSummaryExport($GuardTourLog, $startDate, $endDate, $companyName, $site, $GuardTourLog, $type), 'Day-Wise-Tour-Report.xlsx');
            } else {
                $pdf = PDF::loadView('TourReport.tourDayWise', ['GuardTourLog' => $GuardTourLog, 'startDate' => $startDate, 'endDate' => $endDate, 'companyName' => $companyName, 'site' => $site, 'data' => $GuardTourLog])->setPaper('a4', 'landscape');
                return $pdf->stream('Day-Wise-Tour-Report.pdf');
                // return $pdf->download('Day-Wise-Tour-Report.pdf');
                // return $this->excel->download(new TourSummaryExport($GuardTourLog, $startDate, $endDate, $companyName, $site, $GuardTourLog, $type), 'day_wise_tour_report.pdf');
            }
        } else {
            return redirect()->back()->with('alert', 'Records not found');
        }
    }

    // download guard tour report
    public function downloadGuardTourReport(Request $request)
    {
        $user = session('user');

        ActivityLog::create([
            'date_time' => date('Y-m-d H:i:s'),
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Employee Tour Report",
            'message' => "Employee tour report downloaded by " . $user->name,
        ]);
        $startDate = $request->startDate;
        $endDate = $request->endDate;
        $siteId = $request->siteId;
        $guard = $request->guard;

        $datetime1 = new DateTime($startDate);
        $datetime2 = new DateTime($endDate);
        $reportMonth = $datetime1->format('d M Y') . " to " . $datetime2->format('d M Y');

        $GuardTourLog = GuardTourLog::where('guardId', $guard)->whereBetween('date', [$datetime1->format('Y-m-d'), $datetime2->format('Y-m-d')])->orderBy('date', 'ASC')->orderBy('tourName', 'ASC')->orderBy('round', 'ASC')->get();

        $company = CompanyDetails::where('id', $user->company_id)->first();
        $companyName = $company->name;
        $type = "GuardTourReport";
        $site = SiteDetails::where('id', $siteId)->first();
        $daysCount = 0;
        if (count($GuardTourLog) > 0) {
            if ($request->xlsx == "xlsx") {
                return $this->excel->download(new TourSummaryExport($GuardTourLog, $startDate, $endDate, $companyName, $site, $daysCount, $type), 'Employee-Tour-Report.xlsx');
            } else {
                $pdf = PDF::loadView('TourReport.guardTourReport', ['data' => $GuardTourLog, 'startDate' => $startDate, 'endDate' => $endDate, 'companyName' => $companyName, 'site' => $site, 'daysCount' => $daysCount])->setPaper('a4', 'landscape');
                return $pdf->stream('Employee-Tour-Report.pdf');
                // return $pdf->download('Employee-Tour-Report.pdf');
                // return $this->excel->download(new TourSummaryExport($GuardTourLog, $startDate, $endDate, $companyName, $site, $GuardTourLog, $type), 'day_wise_tour_report.pdf');
            }
        } else {
            return redirect()->back()->with('alert', 'Records not found');
        }
    }



    // guard attendance report
    public function guardAttendanceReport($guardId, $fromDate, $toDate, )
    {
        $user = session('user');
        $userinfo = SiteAssign::where('user_id', $guardId)->first();
        // dd($userinfo , $guardId , "data");
        $startDate = date('Y-m-d', strtotime($fromDate));
        $endDate = date('Y-m-d', strtotime($toDate));
        $datetime1 = new DateTime($startDate);
        $datetime2 = new DateTime($endDate);
        $interval = $datetime1->diff($datetime2);
        $daysCount = (int) $interval->format('%a');
        $daysCount = $daysCount + 1;
        // DB::enableQueryLog();

        $datePresent = Attendance::where('user_id', $guardId)
            ->whereBetween('dateFormat', [$startDate, $endDate])
            ->select('dateFormat')->distinct()->pluck('dateFormat')->toArray();
        // dd(DB::getQueryLog());

        $data = Attendance::where('user_id', $guardId)
            ->whereBetween('dateFormat', [$startDate, $endDate])
            ->get()->groupBy('dateFormat');
        $attendance = Attendance::where('user_id', $guardId)->whereBetween('dateFormat', [$startDate, $endDate])
            ->selectRaw('sum(TIME_TO_SEC(time_calculation)) as actualTime, sum(TIME_TO_SEC(gpsTime)) as gpsTime')->first();

        $actualTime = $attendance->actualTime;
        $gpsTime = $attendance->gpsTime;

        $hours = floor($actualTime / 3600);
        $mins = floor(($actualTime / 60) % 60);

        $gpshours = floor($gpsTime / 3600);
        $gpsmins = floor(($gpsTime / 60) % 60);

        $actualTimeformat = sprintf('%02dhr %02dmin', $hours, $mins);
        $gpsTimeformat = sprintf('%02dhr %02dmin', $gpshours, $gpsmins);

        $company = CompanyDetails::where('id', $user->company_id)->first();
        $companyName = $company->name;

        $days = json_decode($userinfo->weekoff, true);
        $weekOffDates = [];
        if ($days && count($days) > 0) {
            foreach ($days as $key => $value) {
                $utility = new GetDays();
                $monday = $utility->getDays($value, $datetime1->format('F'), $datetime1->format('Y'), $datetime2->format('F'), $datetime2->format('Y'));
                if (gettype($monday) != 'string') {
                    $monday = $monday->format('Y-m-d');
                }
                foreach ($monday as $index => $monday) {
                    $weekOffDates[] = date('d-m-Y', strtotime($monday));
                }
                $lastSundayDate = date('d-m-Y', strtotime('+7 days ' . $weekOffDates[count($weekOffDates) - 1]));
                $lastSunday = new DateTime($lastSundayDate, new DateTimeZone('Asia/Kolkata'));
                if ($lastSunday < $datetime2) {
                    $weekOffDates[] = $lastSundayDate;
                }
            }
        }
        //  dd($datePresent);
        $modaldata = view('AttendanceReport/guardReportView')
            ->with('companyName', $companyName)
            ->with('fromDate', $startDate)
            ->with('toDate', $endDate)
            ->with('daysCount', $daysCount)
            ->with('data', $data)
            ->with('datePresent', $datePresent)
            ->with('weekOffDates', $weekOffDates)
            ->with('actualTimeformat', $actualTimeformat)
            ->with('gpsTimeformat', $gpsTimeformat)
            ->with('attendanceSubType', $attendanceSubType)
            ->with('guardId', $guardId)
            ->with('generatedOn', $this->generatedOn)

            ->render();
        echo $modaldata;
    }


    // download guard report
    public function downloadGuardReport(Request $request)
    {
        $user = session('user');

        // dd($request->all());
        // dump($request->subType , "guard report");\

        if ($request->subType == "Single Supervisor Attendance") {
            $subType = "Single Supervisor Attendance";
        } else {
            $subType = "Employee Attendance Report";
        }

        $flagType = $request->flagType;




        // else if ($request->subType == "Employee Attendance Report") {
        //     $subType = "Employee Attendance Report";
        // } else if ($request->subType == "Employee Attendance Report With Hours") {
        //     $subType = "Employee Attendance Report with Hours";
        // } else {
        //     $subType = "Employee Attendance Report With Site";
        // }



        ActivityLog::create([
            'date_time' => date('Y-m-d H:i:s'),
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Employee Report",
            'message' => "Employee report downloaded by " . $user->name,
        ]);
        $userinfo = SiteAssign::where('user_id', $request->guardId)->first();
        $startDate = date('Y-m-d', strtotime($request->fromDate));
        $endDate = date('Y-m-d', strtotime($request->toDate));
        $datetime1 = new DateTime($startDate);
        $datetime2 = new DateTime($endDate);
        $interval = $datetime1->diff($datetime2);
        $daysCount = (int) $interval->format('%a');
        $daysCount = $daysCount + 1;
        $datePresent = Attendance::where('user_id', $request->guardId)
            ->whereBetween('dateFormat', [$startDate, $endDate])
            ->select('dateFormat')->distinct()->pluck('dateFormat')->toArray();
        $data = Attendance::where('user_id', $request->guardId)
            ->whereBetween('dateFormat', [$startDate, $endDate])
            ->get()->groupBy('dateFormat');
        $attendance = Attendance::where('user_id', $request->guardId)->whereBetween('dateFormat', [$startDate, $endDate])
            ->selectRaw('sum(TIME_TO_SEC(time_calculation)) as actualTime, sum(TIME_TO_SEC(gpsTime)) as gpsTime')->first();

        $actualTime = $attendance->actualTime;
        $gpsTime = $attendance->gpsTime;

        $hours = floor($actualTime / 3600);
        $mins = floor(($actualTime / 60) % 60);

        $gpshours = floor($gpsTime / 3600);
        $gpsmins = floor(($gpsTime / 60) % 60);

        $siteClientNames = [
            'sites' => [],
            'client' => []
        ];

        $clientName = '';
        $siteName = '';
        if ($subType !== 'Single Supervisor Attendance') {
            $clientName = $request->clientName;
            $siteName = $request->siteName;
            // dd($clientName , "cli name");
        } else {

            // dd('in else');

            $siteArray = SiteAssign::where('company_id', $user->company_id)->where('user_id', $user->id)->pluck('site_id')->toArray();
            // dd($siteArray , "site array");
            $site_Ids = json_decode($siteArray[0], true);

            foreach ($site_Ids as $site) {
                $siteName = SiteDetails::where('id', $site)->value('name');
                $siteClientNames['sites'][] = $siteName;

                $clientNames = SiteAssign::where('site_id', $site)->value('client_name');
                $siteClientNames['client'][] = $clientNames;
                // dd($siteClientNames , "clientNames");
            }
            // dd($siteClientNames , "site clients names");
            // dd($site_Ids, "site ids");

            $siteClientNames['sites'] = array_unique($siteClientNames['sites']);
            $siteClientNames['client'] = array_unique($siteClientNames['client']);
        }

        // dump($request->clientName , $request->siteName , "name");

        $actualTimeformat = sprintf('%02dhr %02dmin', $hours, $mins);
        $gpsTimeformat = sprintf('%02dhr %02dmin', $gpshours, $gpsmins);

        $company = CompanyDetails::where('id', $user->company_id)->first();
        $companyName = $company->name;
        $guardId = $request->guardId;
        $attendanceSubType = $request->attendanceSubType;
        // dd($attendanceSubType , 'attendanceSubType');
        $days = json_decode($userinfo->weekoff, true);
        $weekOffDates = [];
        if ($days && count($days) > 0) {
            foreach ($days as $key => $value) {
                $utility = new GetDays();
                $mondays = $utility->getDays($value, $datetime1->format('F'), $datetime1->format('Y'), $datetime2->format('F'), $datetime2->format('Y'));
                foreach ($mondays as $index => $monday) {
                    // dd($monday);
                    if (gettype($monday) != 'string') {
                        $monday = $monday->format('Y-m-d');
                    }
                    if (strtotime(date('Y-m-d', strtotime($monday))) > strtotime(date('Y-m-d'))) {
                        break;
                    } elseif (strtotime(date('Y-m-d', strtotime($monday))) < strtotime($startDate)) {
                        // break;
                    } else {
                        $weekOffDates[] = date('d-m-Y', strtotime($monday));
                    }
                }

                if (count($weekOffDates) > 0) {
                    $lastSundayDate = date('d-m-Y', strtotime('+7 days ' . $weekOffDates[count($weekOffDates) - 1]));
                    $lastSunday = new DateTime($lastSundayDate, new DateTimeZone('Asia/Kolkata'));
                    if ($lastSunday <= $datetime2) {
                        $weekOffDates[] = $lastSundayDate;
                    }
                }
            }
        }
        $flag = $request->xlsx;
        if ($request->xlsx == "xlsx") {
            // dd($subType , "sub");
            return $this->excel->download(new GuardAttendanceExport(
                $companyName,
                $datePresent,
                $startDate,
                $endDate,
                $daysCount,
                $weekOffDates,
                $actualTimeformat,
                $gpsTimeformat,
                $data,
                $guardId,
                $flag,
                $subType,
                $clientName,
                $siteName,
                $attendanceSubType,
                $this->generatedOn,
                $siteClientNames,
                $flagType
            ), $subType . '.xlsx');
        } else {
            // return $this->excel->download(new GuardAttendanceExport($companyName, $datePresent, $startDate, $endDate, $daysCount, $weekOffDates, $actualTimeformat, $gpsTimeformat, $data), 'guard_attendance_report.pdf');
            $pdf = PDF::loadView(
                'AttendanceReport.guardReport',
                [
                    'subType' => $subType,
                    'companyName' => $companyName,
                    'datePresent' => $datePresent,
                    'fromDate' => $startDate,
                    'toDate' => $endDate,
                    'daysCount' => $daysCount,
                    'weekOffDates' => $weekOffDates,
                    'actualTimeformat' => $actualTimeformat,
                    'gpsTimeformat' => $gpsTimeformat,
                    'data' => $data,
                    'guardId' => $guardId,
                    'flag' => $flag,
                    'attendanceSubType' => $attendanceSubType,
                    'clientName' => $clientName,
                    'siteName' => $siteName,
                    'generatedOn' => $this->generatedOn,
                    'siteClientNames' => $siteClientNames,
                    'flagType' => $flagType
                ]
            )->setPaper('a4', 'landscape');
            return $pdf->stream($subType);
            // return $pdf->download('Employee-Attendance-Report.pdf');
        }
    }



    // single day tour
    public function singleDayTour($guardTourLogID, $date)
    {
        $tourDate = date('Y-m-d', strtotime($date));
        $GuardTourLogs = GuardTourLog::where('id', $guardTourLogID)->where('date', $tourDate)->first();
        $GuardTourLog = GuardTour::where('id', $GuardTourLogs->tourId)->first();
        $site = $GuardTourLogs->site_id;
        //dd($GuardTourLog);
        return view('TourReport/singleDayTourView')->with('guardTourLogID', $guardTourLogID)->with('tourDate', $tourDate)->with('GuardTourLog', $GuardTourLog)->with('geofences', $site)->with('userId', $GuardTourLogs->guardId)->with('tourName', $GuardTourLogs);
    }

    //download single day tour report
    public function downloadsingleDayTour(Request $request)
    {
        $user = session('user');

        $tourDate = date('Y-m-d', strtotime($request->tourDate));
        $GuardTourLogs = GuardTourLog::where('id', $request->guardTourLogID)->where('date', $tourDate)->first();
        $GuardTourLog = GuardTour::where('id', $GuardTourLogs->tourId)->first();
        $type = "singleDayTour";
        $date = 0;
        $userId = $GuardTourLogs->guardId;
        $geo = $GuardTourLog->site_id;
        if ($GuardTourLog) {
            ActivityLog::create([
                'date_time' => date('Y-m-d H:i:s'),
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'type' => "Single Day Tour",
                'message' => "Single day tour report downloaded by " . $user->name,
            ]);
            if ($request->xlsx == "xlsx") {
                return $this->excel->download(new TourExport($GuardTourLog, $tourDate, $date, $userId, $geo, $type), 'Single-Day-Tour-Report.xlsx');
            } else {
                $pdf = PDF::loadView('TourReport.singleDayTour', ['GuardTourLog' => $GuardTourLog, 'tourDate' => $tourDate])->setPaper('a4', 'landscape');
                return $pdf->stream('Single-Day-Tour-Report.pdf');
                // return $pdf->download('Single-Day-Tour-Report.pdf');
                // return $this->excel->download(new TourExport($GuardTourLog, $tourDate, $date, $userId, $geo, $type), 'guard_day_wise_tour_report.pdf');
            }
        } else {
            return redirect()->back()->with('alert', 'Records not found');
        }
    }

    //download emergency attendancce report
    public function downloadEmergencyAttendance(Request $request)
    {
        $user = session('user');

        $startDate = date('Y-m-d', strtotime($request->startDate));
        $endDate = date('Y-m-d', strtotime($request->endDate));
        $datetime1 = new DateTime($startDate);
        $datetime2 = new DateTime($endDate);
        $geofences = $request->geofences;
        $reportMonth = $datetime1->format('d M Y') . " to " . $datetime2->format('d M Y');
        $data = Attendance::where('site_id', $geofences)->where('emergency_attend', 1)->whereBetween('dateFormat', [$datetime1->format('Y-m-d'), $datetime2->format('Y-m-d')])->get();
        $company = CompanyDetails::where('id', $user->company_id)->first();
        $companyName = $company->name;
        $site = SiteDetails::where('id', $geofences)->first();
        $type = "emergency";
        $guard = 0;
        $daysCount = 0;
        $flag = $request->xlsx;
        if (count($data) > 0) {
            ActivityLog::create([
                'date_time' => date('Y-m-d H:i:s'),
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'type' => "Emergency Attendance Report",
                'message' => "Emergency attendance report downloaded by " . $user->name,
            ]);
            if ($request->xlsx == "xlsx") {
                return $this->excel->download(new GuardAbsentExport($data, $site, $companyName, $startDate, $endDate, $reportMonth, $guard, $daysCount, $type, $flag), 'On-Site-Attendance-Report.xlsx');
            } else {
                $pdf = PDF::loadView('AttendanceReport.emergencyAttendanceReport', ['data' => $data, 'site' => $site, 'companyName' => $companyName, 'dateRange' => $reportMonth, 'guard' => $guard, 'daysCount' => $daysCount, 'type' => $type, 'flag' => $flag])->setPaper('a4', 'landscape');
                // return $pdf->download('On-Site-Attendance-Report.pdf');
                return $pdf->stream('On-Site-Attendance-Report.pdf');
                // return $this->excel->download(new GuardAbsentExport($data, $site, $companyName, $reportMonth, $guard, $daysCount, $type), 'emergency_attendance_report.pdf');
            }
        } else {
            return redirect()->back()->with('alert', 'Records not found');
        }
    }

    // download forgot to mark exit report
    public function downloadForgotToMarkExit(Request $request)
    {
        $user = session('user');
        $startDate = date('Y-m-d', strtotime($request->startDate));
        $endDate = date('Y-m-d', strtotime($request->endDate));
        $datetime1 = new DateTime($startDate);
        $datetime2 = new DateTime($endDate);
        $geofences = $request->geofences;
        $client = $request->client;
        $clientName = $request->clientName;
        // dump($clientName  , "nameo");
        $reportMonth = $datetime1->format('d M Y') . " to " . $datetime2->format('d M Y');
        $subType = "Forgot To Mark Exit";
        $type = "forgotToMarkExit";
        $company = CompanyDetails::where('id', $user->company_id)->first();
        $companyName = $company ? $company->name : "Unknown Company";
        $flag = $request->xlsx;



        $attendanceQuery = Attendance::where('attendance.company_id', $user->company_id)
            ->leftjoin('site_assign as site', 'attendance.user_id', '=', 'site.user_id')
            ->whereBetween('attendance.dateFormat', [$startDate, $endDate])
            ->whereNotIn('site.role_id', [1, 2, 7, 4])
            ->whereNull('exit_time')
            ->orderBy('attendance.name')
            ->orderBy('attendance.dateFormat');

        // Filter based on user role
        if ($user->role_id == 7) {
            $siteQuery = SiteAssign::where('company_id', $user->company_id);

            // dump('in else if');
            // Fetch site assignments for the user
            $siteAssigned = SiteAssign::where('user_id', $user->id)->first();

            // $client_ids = json_decode($siteAssigned, true);
            $client_ids = json_decode($siteAssigned['site_id'], true);
            // dd($siteAssigned ,  $client_ids,"site Assigned");

            if ($client == 'all') {
                $siteArray = SiteDetails::whereIn('client_id', $client_ids)->pluck('id')->toArray();
                $userIds = SiteAssign::whereIn('client_id', $client_ids)->pluck('user_id')->toArray();
            } elseif ($client !== 'all' && $geofences == 'all') {

                $siteArray = SiteDetails::where('client_id', $client)->pluck('id')->toArray();
                $userIds = SiteAssign::where('client_id', $client)->pluck('user_id')->toArray();
            } else if ($client !== 'all' && $geofences !== 'all') {

                $siteArray = SiteDetails::where('client_id', $client)->pluck('id')->toArray();
                $userIds = SiteAssign::where('site_id', $geofences)->pluck('user_id')->toArray();
            }



            // dd($siteArray , "array");

            $supervisorIds = Users::where([['users.company_id', '=', $user->company_id], ['users.role_id', '=', 2]])
                ->leftJoin('site_assign as site', 'users.id', '=', 'site.user_id')
                ->where(function ($query) use ($siteArray) {
                    foreach ($siteArray as $siteId) {
                        $query->orWhereRaw('JSON_CONTAINS(site.site_id, ?)', [json_encode([$siteId])]);
                    }
                })->pluck('site.user_id')->toArray();


            // dd($userIds, "user ids");

            $site_UserIds = array_merge($userIds, $supervisorIds);

            // dump($siteQuery->pluck('user_name')->toArray());
            $siteQuery->when(
                $geofences !== 'all' && $geofences !== null,
                fn($query) => $query->where('site_id', $geofences)

            )
                ->whereIn('user_id', $site_UserIds);

            // dd($siteQuery->pluck('client_id')->toArray());
            // dd($siteQuery->pluck('user_id')->toArray());
        } else if ($user->role_id == 1) {
            if ($client == "all") {
                $site_UserIds = SiteAssign::where('company_id', $user->company_id)->whereNotIn('role_id', [1, 4])
                    ->pluck('user_id')->unique();
            } elseif ($client != "all" && $geofences != "all") {
                $site_UserIds = SiteAssign::where('client_id', $client)
                    ->where('site_id', $geofences)
                    ->pluck('user_id')->unique();
                // dump($site_UserIds , "usr id");
            } elseif ($client != "all" && $geofences == "all") {
                $site_UserIds = SiteAssign::where('client_id', $client)
                    ->pluck('user_id')->unique();
            } else {
                $site_UserIds = [];
            }
        } else {
            // dump('the role id is 2');
            $siteIds = $user->role_id == 2
                ? SiteAssign::where('user_id', $user->id)
                    ->pluck('site_id')
                    ->map(function ($item) {
                        return is_string($item) ? json_decode($item, true) : $item;
                    })
                    ->flatten()
                    ->unique()
                    ->values()
                : collect();
            // dump($siteIds, "siteIds arr");

            $site_UserIds_query = SiteAssign::where('company_id', $user->company_id)->whereIn('site_id', $siteIds);
            // dd($site_UserIds);

            if ($client == 'all') {
                $site_UserIds = $site_UserIds_query->pluck('user_id');
            } else if ($client !== 'all' && $geofences == 'all') {
                // dump($client, $geofences, "client");
                // $attendanceQuery->whereIn('user_id', $site_UserIds_query->pluck('user_id'));
                $site_UserIds = $site_UserIds_query->pluck('user_id');
            } else if ($client !== 'all' && $geofences !== 'all') {
                $site_UserIds = $site_UserIds_query->where('site_id', $geofences)->pluck('user_id');
            }
        }

        if (!empty($site_UserIds)) {
            $attendanceQuery->whereIn('attendance.user_id', $site_UserIds);
        }
        $data = $attendanceQuery->get();



        // Handle site and client filtering
        // dd($request->all());
        $siteInfo = [
            'name' => 'All Sites',
            'client' => [
                'id' => $client,
                'name' => 'All Clients'
            ]
        ];

        if ($client !== 'all' && $geofences == 'all') {
            $clientDetails = ClientDetails::find($client);
            $siteInfo['client']['name'] = $clientDetails->name ?? 'All Client';
            // dump($siteInfo , "siteInfo");

        } else {
            $clientDetails = ClientDetails::find($client);
            $site = SiteDetails::where('id', $geofences)->first();
            // dd($site->site_name , "site");
            // dump($site );
            $siteInfo['client']['name'] = $clientDetails->name ?? 'All Client';
            $siteInfo['name'] = $site->name ?? 'All site';
            // dump($siteInfo ,"site Info");
        }




        // dd($siteInfo['client']['name'], "name");
        // Check if data exists
        if ($data->count() > 0) {
            // Log activity
            ActivityLog::create([
                'date_time' => now(),
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'type' => "Forgot to mark exit Report",
                'message' => "Forgot to mark exit report downloaded by " . $user->name,
            ]);

            // dd($request->all(), "client name");
            // dd(                    $siteInfo['client']['name'] , "client Name");
            // Handle file export
            if ($flag == "xlsx") {
                return $this->excel->download(new ForgotToMarkExitExport(
                    $data,
                    $subType,
                    $siteInfo['name'],
                    $startDate,
                    $endDate,
                    $companyName,
                    $reportMonth,
                    0, // Guard
                    0, // Days count
                    $flag,
                    $client,
                    $siteInfo['client']['name'],
                    $this->generatedOn
                ), 'Forgot-To-Mark-Exit-Report.xlsx');
            } else {
                // Generate PDF
                $pdf = PDF::loadView(
                    'AttendanceReport.forgotToExit',
                    [
                        'data' => $data,
                        'subType' => $subType,
                        'site' => $siteInfo['name'],
                        'clientName' => $siteInfo['client']['name'],
                        'companyName' => $companyName,
                        'dateRange' => $reportMonth,
                        'guard' => 0,
                        'daysCount' => 0,
                        'type' => $type,
                        'flag' => $flag,
                        'client' => $client,
                        'clientName' => $request->clientName,
                        'generatedOn' => $this->generatedOn
                    ]
                )->setPaper('a4', 'portrait');

                return $pdf->stream('Forgot-To-Mark-Exit-Report.pdf');
            }
        }

        // Redirect if no records found
        return redirect()->back()->with('alert', 'Records not found');
    }



    // performance report
    public function PerformanceReport($fromDate, $toDate, $geofences)
    {
        $user = session('user');
        $geofences = $geofences;
        $startDate = date('Y-m-d', strtotime($fromDate));
        $endDate = date('Y-m-d', strtotime($toDate));
        $datetime1 = new DateTime($startDate);
        $datetime2 = new DateTime($endDate);
        $interval = $datetime1->diff($datetime2);
        $daysCount = (int) $interval->format('%a');
        $daysCount = $daysCount + 1;
        $reportMonth = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));
        $data = SiteAssign::where('site_id', $geofences)->orderBy('user_name', 'ASC')->get();
        // dd($daysCount);
        $company = CompanyDetails::where('id', $user->company_id)->first();
        $companyName = $company->name;

        $site = SiteDetails::find($geofences);

        return view('performanceReport/siteWisePerformanceReportView')->with('daysCount', $daysCount)->with('data', $data)->with('fromDate', $startDate)->with('toDate', $endDate)->with('reportMonth', $reportMonth)->with('companyName', $companyName)->with('site', $site)->with('datetime1', $datetime1)->with('datetime2', $datetime2)->with('geofences', $geofences);
    }

    // download performance report
    public function downloadPerformanceReport(Request $request)
    {
        $user = session('user');

        $geofences = $request->geofences;
        $startDate = date('Y-m-d', strtotime($request->fromDate));
        $endDate = date('Y-m-d', strtotime($request->toDate));
        $datetime1 = new DateTime($startDate);
        $datetime2 = new DateTime($endDate);
        $interval = $datetime1->diff($datetime2);
        $daysCount = (int) $interval->format('%a');
        $daysCount = $daysCount + 1;
        $reportMonth = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));
        $data = SiteAssign::where('site_id', $geofences)->orderBy('user_name', 'ASC')->get();
        $company = CompanyDetails::where('id', $user->company_id)->first();
        $companyName = $company->name;
        $flag = $request->xlsx;
        $site = SiteDetails::find($request->geofences);
        if (count($data) > 0) {
            ActivityLog::create([
                'date_time' => date('Y-m-d H:i:s'),
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'type' => "Performance Report",
                'message' => "Performance report downloaded by " . $user->name,
            ]);
            if ($request->xlsx == "xlsx") {
                return $this->excel->download(new PerformanceExport($daysCount, $data, $startDate, $endDate, $reportMonth, $companyName, $site, $datetime1, $datetime2, $flag), 'Performance-Report.xlsx');
            } else {
                $pdf = PDF::loadView('performanceReport/siteWisePerformanceReport', ['daysCount' => $daysCount, 'data' => $data, 'fromDate' => $startDate, 'toDate' => $endDate, 'reportMonth' => $reportMonth, 'companyName' => $companyName, 'site' => $site, 'datetime1' => $datetime1, 'datetime2' => $datetime2, 'flag' => $flag])->setPaper('a4', 'portrait');
                return $pdf->stream('Performance-Report.pdf');
                // return $pdf->download('Performance-Report.pdf');
            }
        }
    }


    //download all supervisor report
    public function downloadAllSupervisorAttendance(Request $request)
    {
        $user = session('user');
        // dd($request);
        //if($request->subType == "supervisor") {
        $subType = "Supervisor Attendance Report";
        //} else{
        //$subType = '';
        //}

        ActivityLog::create([
            'date_time' => date('Y-m-d H:i:s'),
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "Attendance Report SiteWise",
            'message' => "Sitewise attendance report downloaded by " . $user->name,
        ]);
        $startDate = $request->fromdate;
        $endDate = $request->todate;
        $geofences = $request->geofences;
        $datetime1 = new DateTime($startDate);
        $datetime2 = new DateTime($endDate);
        $interval = $datetime1->diff($datetime2);
        $daysCount = (int) $interval->format('%a');
        $daysCount = $daysCount + 1;
        $dateFormat = "Range";
        $startDatee = date('d-m-Y', strtotime($startDate));
        $currentDate = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
        $currentDate = $currentDate->format("d-m-Y");
        $data = SiteDetails::where('id', $request->geofences)->get();
        $companyData = CompanyDetails::where('id', $user->company_id)->first();
        $companyName = $companyData->name;
        $date = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));

        // dd($startDate,$endDate);

        // $test = Users::where('users.company_id', $user->company_id)
        //     ->rightjoin('attendance', 'users.id', '=', 'attendance.user_id')
        //     // ->rightjoin('site_assign', 'users.id', '=', 'site_assign.user_id')
        //     ->whereBetween('attendance.dateFormat', [$startDate, $endDate])
        //     ->where('users.role_id', 2)
        //     ->select('users.name as name', 'users.id as user_id', 'attendance.date as date', 'attendance.site_name as site_name')
        //     ->orderBy('name');

        // if ($request->supervisorSelect != "all") {
        //     $site_UserIds = SiteAssign::where('user_id', $request->supervisorSelect)->get()->pluck('user_id')->unique();
        // } else {
        //     $site_UserIds = [];
        // }


        $site_UserIds = [];
        if ($user->role_id == 1) {

            if ($request->supervisorSelect == "all") {
                $site_UserIds = SiteAssign::where('company_id', $user->company_id)->where('role_id', 2)->get()->pluck('user_id')->unique();
            } else {
                $site_UserIds = SiteAssign::where('company_id', $user->company_id)->where('user_id', $request->supervisorSelect)->where('role_id', 2)->get()->pluck('user_id')->unique();
            }
        } else if ($user->role_id == 7) {
            $siteQuery = SiteAssign::where('company_id', $user->company_id);


            // dump('in else if');
            // Fetch site assignments for the user
            $siteAssigned = SiteAssign::where('user_id', $user->id)->first();

            // $client_ids = json_decode($siteAssigned, true);
            $client_ids = json_decode($siteAssigned['site_id'], true);
            // dd($siteAssigned ,  $client_ids,"site Assigned");

            if ($request->supervisorSelect == 'all') {
                // dump('all');
                $siteArray = SiteDetails::whereIn('client_id', $client_ids)->pluck('id')->toArray();
                $supervisorIds = Users::where([['users.company_id', '=', $user->company_id], ['users.role_id', '=', 2]])
                    ->leftJoin('site_assign as site', 'users.id', '=', 'site.user_id')
                    ->where(function ($query) use ($siteArray) {
                        foreach ($siteArray as $siteId) {
                            $query->orWhereRaw('JSON_CONTAINS(site.site_id, ?)', [json_encode([$siteId])]);
                        }
                    })->pluck('site.user_id')->toArray();
                $site_UserIds = $supervisorIds;
            } else {
                $site_UserIds = SiteAssign::where('company_id', $user->company_id)->where('user_id', $request->supervisorSelect)->where('role_id', 2)->get()->pluck('user_id')->unique();

                // dump('in else', $site_UserIds, $request->supervisorSelect);
            }


            // dd($siteArray , "array");



            // dd($siteArray , $supervisorIds , "ids");
            // dd($userIds, "user ids");


            // dd($site_UserIds, "ids");
            // dump($siteQuery->pluck('user_name')->toArray());
            $siteQuery->when(
                $geofences !== 'all' && $geofences !== null,
                fn($query) => $query->where('site_id', $geofences)

            )
                ->whereIn('user_id', $site_UserIds);

            // dd($siteQuery->pluck('client_id')->toArray());
            // dd($siteQuery->pluck('user_id')->toArray());


        }

        $test = Users::where('users.company_id', $user->company_id)
            ->whereIn('users.id', $site_UserIds)
            ->where('users.role_id', 2)
            ->leftJoin('attendance', function ($join) use ($startDate, $endDate) {
                $join->on('users.id', '=', 'attendance.user_id')
                    ->whereBetween('attendance.dateFormat', [$startDate, $endDate]);
            })
            ->select('users.name as name', 'users.id as user_id', 'attendance.dateFormat as date', 'attendance.site_name as site_name', 'attendance.duration_for_calc as duration', 'attendance.entry_time', 'attendance.exit_date_time', 'attendance.gpsTime')
            ->orderBy('name');

        // dd($test->pluck('name')->unique());
        // dd($test);
        $names = $test->groupBy(['user_id', 'name', 'date'])
            ->get()->mapToGroups(function ($item, $key) {
                return [$item['user_id'] => $item['name']];
            })->toArray();


        $sites = $test->groupBy(['user_id', 'name', 'date', 'site_name'])
            ->get()->mapToGroups(function ($item, $key) {
                return [$item['user_id'] => $item['site_name']];
            })->toArray();
        // dump($test , $sites , "site data");
        $userIds = array_unique($test->pluck('user_id')->toArray());
        $weekoffData = SiteAssign::whereIn('user_id', $userIds)->groupBy(['user_id', 'weekoff']);


        $clients = $weekoffData->get()->mapToGroups(function ($item, $key) {
            return [$item['user_id'] => $item['client_name']];
        })->toArray();


        $weekoffs = $weekoffData->get()->mapToGroups(function ($item, $key) {
            return [$item['user_id'] => $item['weekoff']];
        })->toArray();

        $data = $test->groupBy(['user_id', 'name', 'date'])
            ->get()->mapToGroups(function ($item, $key) {
                return [$item['user_id'] => $item['date']];
            })->toArray();


        // dd($data);
        // dump($test , $data, $sites , "site data");

        $userIdsArrays = Users::where('company_id', $user->company_id)->where('role_id', 2)->where('showUser', 1)->pluck('id')->toArray();
        $data1 = Attendance::whereBetween('dateFormat', [$startDate, $endDate])->whereIn('user_id', $userIdsArrays)->where('company_id', $user->company_id);

        $attendance = Attendance::whereBetween('dateFormat', [$startDate, $endDate])
            ->selectRaw('sum(TIME_TO_SEC(time_calculation)) as actualTime, sum(TIME_TO_SEC(gpsTime)) as gpsTime')->get();
        $actualTime = $attendance[0]['actualTime'];
        //dd($actualTime);
        $gpsTime = $attendance[0]['gpsTime'];
        //dd($data);

        $hours = floor($actualTime / 3600);
        $mins = floor(($actualTime / 60) % 60);
        $gpshours = floor($gpsTime / 3600);
        $gpsmins = floor(($gpsTime / 60) % 60);

        $actualTimeformat = sprintf('%02dhr %02dmin', $hours, $mins);
        $gpsTimeformat = sprintf('%02dhr %02dmin', $gpshours, $gpsmins);
        $attendCount = $data1->groupBy(['user_id', 'dateFormat'])
            ->get()->mapToGroups(function ($item, $key) {
                return [$item['dateFormat'] => $item['user_id']];
            })->toArray();
        ksort($attendCount);
        $companyId = $companyData->id;
        $flag = $request->xlsx;


        $supervisorAssignedSites = SiteAssign::where('company_id', $user->company_id)
            ->where('role_id', 2)
            ->select('user_id', 'site_id')
            ->get();

        $supervisorSites = [];
        $allSiteIds = [];

        foreach ($supervisorAssignedSites as $assign) {
            $ids = is_string($assign->site_id) ? json_decode($assign->site_id, true) : (array) $assign->site_id;
            if (!empty($ids)) {
                $allSiteIds = array_merge($allSiteIds, $ids);
            }
        }

        $allSiteIds = array_unique($allSiteIds);
        $sitesMap = !empty($allSiteIds) ? SiteDetails::whereIn('id', $allSiteIds)->get()->groupBy('id') : collect();

        $fallbackClientIds = $sitesMap->flatten()->pluck('client_id')->filter()->unique()->toArray();
        $clientsMap = !empty($fallbackClientIds) ? ClientDetails::whereIn('id', $fallbackClientIds)->pluck('name', 'id')->toArray() : [];

        foreach ($supervisorAssignedSites as $assign) {
            $ids = is_string($assign->site_id) ? json_decode($assign->site_id, true) : (array) $assign->site_id;
            if (empty($ids))
                continue;

            $siteNames = [];
            $clientNames = [];
            $lookupClientIds = [];

            foreach ($ids as $sid) {
                if (isset($sitesMap[$sid])) {
                    $site = $sitesMap[$sid]->first();
                    $siteNames[] = $site->name;
                    if ($site->client_name) {
                        $clientNames[] = $site->client_name;
                    } else if ($site->client_id) {
                        $lookupClientIds[] = $site->client_id;
                    }
                }
            }

            if (!empty($lookupClientIds)) {
                foreach ($lookupClientIds as $cid) {
                    if (isset($clientsMap[$cid]))
                        $clientNames[] = $clientsMap[$cid];
                }
            }

            $supervisorSites[$assign->user_id] = [
                'site' => array_values(array_unique($siteNames)),
                'client' => array_values(array_unique($clientNames))
            ];
        }

        // dump($attendCount , "attend count");
        if ($request->xlsx == "xlsx") {

            return $this->excel->download(new ClientSupervisorExport(
                $data,
                $weekoffs,
                $names,
                $attendCount,
                $date,
                $daysCount,
                $startDatee,
                $companyName,
                $startDate,
                $endDate,
                $currentDate,
                $companyId,
                $dateFormat,
                $geofences,
                $flag,
                $actualTimeformat,
                $gpsTimeformat,
                $subType,
                $sites,
                $clients,
                $supervisorSites,
                $this->generatedOn
            ), 'All-Supervisor-Report.xlsx');

            //return $this->excel->download(new ClientSupervisorExport($data, $weekoffs, $names, $attendCount, $date, $daysCount, $startDatee, $companyName, $startDate, $endDate, $currentDate, $companyId, $dateFormat, $geofences, $flag, $subType), 'Site-Wise-Employee-Report.xlsx');
        } else {
            $customPaper = array(0, 0, 500, 3900);
            $pdf = PDF::loadView('reports.clientSupervisorReport', [
                'data' => $data,
                'weekoffs' => $weekoffs,
                'names' => $names,
                'attendCount' => $attendCount,
                'date' => $date,
                'daysCount' => $daysCount,
                'startDatee' => $startDatee,
                'companyName' => $companyName,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'currentDate' => $currentDate,
                'companyId' => $companyId,
                'dateFormat' => $dateFormat,
                'geofences' => $geofences,
                'flag' => $flag,
                'actualTimeformat' => $actualTimeformat,
                'gpsTimeformat' => $gpsTimeformat,
                'subType' => $subType,
                'sites' => $sites,
                'clients' => $clients,
                'supervisorSites' => $supervisorSites,
                'generatedOn' => $this->generatedOn

            ])->setPaper($customPaper, 'landscape');
            return $pdf->stream('All-Supervisor-Report.pdf');
            // return $pdf->download('Site-Wise-Employee-Report.pdf');
            // return $this->excel->download(new GuardMonthlyExport($companyName, $startDate, $endDate, $daysCount, $data, $datePresent, $weekOffDates, $actualTimeformat, $gpsTimeformat), 'supervisor_report.pdf');
        }
    }




    // download all guard attendance report

    private function fetchSiteData($user, $request, $site_UserIds, $geofences, $client)
    {
        $query = SiteAssign::where('company_id', $user->company_id)
            ->select('user_id', 'site_name', 'client_name');

        if (!empty($site_UserIds)) {
            $query->whereIn('user_id', $site_UserIds);
        }

        return $query->get()
            ->mapToGroups(function ($item) {
                return [
                    $item['user_id'] => [
                        'site' => $item['site_name'],
                        'client' => $item['client_name']
                    ]
                ];
            })
            ->toArray();
    }

    private function fetchAttendanceData($user, $startDate, $endDate, $site_UserIds, $geofences)
    {
        return Attendance::where('company_id', $user->company_id)
            ->whereIn('user_id', $site_UserIds)
            ->whereBetween('dateFormat', [$startDate, $endDate])
            ->whereIn('role_id', [2, 3]);
    }


    public function downloadAllGuardAttendance(Request $request)
    {
        $user = session('user');
        $startDate = date('Y-m-d', strtotime($request->fromdate));
        $endDate = date('Y-m-d', strtotime($request->todate));
        $guard = $request->guard;
        $geofences = $request->geofences;
        $client = $request->client;
        $subType = $request->subType;
        $generatedOn = now()->format('d F Y , h:i:a');
        // Calculate days count consistently with view controller
        $datetime1 = new DateTime($request->fromdate);
        $datetime2 = new DateTime($request->todate);
        $interval = $datetime1->diff($datetime2);
        $daysCount = (int) $interval->format('%a') + 1;

        // Set date formats consistently
        $dateFormat = "Range";
        $date = date('d M Y', strtotime($request->fromdate)) . " to " . date('d M Y', strtotime($request->todate));
        $startDatee = date('d-m-Y', strtotime($request->fromdate));
        $currentDate = (new DateTime('now', new DateTimeZone('Asia/Kolkata')))->format("d-m-Y");


        // Determine site_UserIds based on user role\
        if ($user->role_id == 7) {

            // Fetch site assignments for the user
            $siteAssigned = SiteAssign::where('user_id', $user->id)->first();

            // $client_ids = json_decode($siteAssigned, true);
            $client_ids = json_decode($siteAssigned['site_id'], true);
            // dd($siteAssigned ,  $client_ids,"site Assigned");

            $siteArray = SiteDetails::whereIn('client_id', $client_ids)->pluck('id')->toArray();
            // dd($siteArray , "array");

            $supervisorIds = Users::where([['users.company_id', '=', $user->company_id], ['users.role_id', '=', 2]])
                ->leftJoin('site_assign as site', 'users.id', '=', 'site.user_id')
                ->where(function ($query) use ($siteArray) {
                    foreach ($siteArray as $siteId) {
                        $query->orWhereRaw('JSON_CONTAINS(site.site_id, ?)', [json_encode([$siteId])]);
                    }
                })->pluck('user_id')->toArray();

            $userIds = SiteAssign::whereIn('client_id', $client_ids)->pluck('user_id')->toArray();

            // $userIds = array_merge($userIds, $supervisorIds);
            $site_UserIds = array_merge($userIds, $supervisorIds);
        } else if ($user->role_id == 1) {
            $site_UserIds = SiteAssign::where('company_id', $user->company_id)
                ->whereNotIn('role_id', [1, 4])
                ->get()
                ->pluck('user_id')
                ->unique();
        } else {
            $site = SiteAssign::where('user_id', $user->id)->first();
            $siteArray = json_decode($site['site_id'], true);
            $site_UserIds = SiteAssign::where('company_id', $user->company_id)
                ->whereIn('site_id', $siteArray)
                ->where('role_id', 3)
                ->get()
                ->pluck('user_id')
                ->unique();
        }

        // dump($site_UserIds , "sit user ids");

        // Base query for attendance data
        $testQuery = Users::where('users.company_id', $user->company_id)
            ->rightjoin('attendance', 'users.id', '=', 'attendance.user_id')
            ->rightjoin('site_assign', 'users.id', '=', 'site_assign.user_id')
            ->whereIn('users.id', $site_UserIds)
            ->whereBetween('attendance.dateFormat', [$startDate, $endDate])
            ->select(
                'users.name as name',
                'users.id as user_id',
                'attendance.date as date',
                'attendance.site_name as site_name',
                'attendance.time_difference as duration',
                'site_assign.client_name as client_name'
            )
            ->orderBy('name');

        $allData = $testQuery->get();

        // Process data using collection methods
        $groupedData = $allData->groupBy('user_id');
        $names = $groupedData->map(fn($group) => $group->pluck('name')->unique()->values())->toArray();
        $hours = $groupedData->map(fn($group) => $group->unique('date')->values()->pluck('duration'))->toArray();
        $data = $groupedData->map(fn($group) => $group->pluck('date')->unique()->values()->toArray())->toArray();
        $attendSites = $allData->mapToGroups(function ($item) {
            return [
                $item['user_id'] => [
                    'site' => $item['site_name'],
                    'client' => $item['client_name']
                ]
            ];
        })->toArray();

        $userIds = array_keys($names);

        // Fetch site data consistently
        $sites = $this->fetchSiteData($user, $request, $site_UserIds, $geofences, $client);

        // Batch fetch supervisor/admin site assignments to eliminate N+1 queries
        $supervisorAssignedSites = SiteAssign::where('company_id', $user->company_id)
            ->whereIn('role_id', [7, 2])
            ->select('user_id', 'site_id', 'role_id')
            ->get();

        $supervisorSites = [];
        $allSiteIds = [];
        $allAdminClientIds = [];

        foreach ($supervisorAssignedSites as $assign) {
            $ids = is_string($assign->site_id) ? json_decode($assign->site_id, true) : (array) $assign->site_id;
            if (empty($ids))
                continue;

            if ($assign->role_id === 7) {
                $allAdminClientIds = array_merge($allAdminClientIds, $ids);
            } else {
                $allSiteIds = array_merge($allSiteIds, $ids);
            }
        }

        $allSiteIds = array_unique($allSiteIds);
        $sitesMap = !empty($allSiteIds) ? SiteDetails::whereIn('id', $allSiteIds)->get()->groupBy('id') : collect();

        $fallbackClientIds = $sitesMap->flatten()->pluck('client_id')->filter()->unique()->toArray();
        $clientIdsToFetch = array_unique(array_merge($allAdminClientIds, $fallbackClientIds));
        $clientsMap = !empty($clientIdsToFetch) ? ClientDetails::whereIn('id', $clientIdsToFetch)->pluck('name', 'id')->toArray() : [];

        foreach ($supervisorAssignedSites as $assign) {
            $ids = is_string($assign->site_id) ? json_decode($assign->site_id, true) : (array) $assign->site_id;
            if (empty($ids))
                continue;

            if ($assign->role_id === 7) {
                $clientNames = [];
                foreach ($ids as $cid) {
                    if (isset($clientsMap[$cid]))
                        $clientNames[] = $clientsMap[$cid];
                }
                $supervisorSites[$assign->user_id] = [
                    'site' => [],
                    'client' => array_values(array_unique($clientNames))
                ];
            } else {
                $siteNames = [];
                $clientNames = [];
                $lookupClientIds = [];

                foreach ($ids as $sid) {
                    if (isset($sitesMap[$sid])) {
                        $site = $sitesMap[$sid]->first();
                        $siteNames[] = $site->name;
                        if ($site->client_name) {
                            $clientNames[] = $site->client_name;
                        } else if ($site->client_id) {
                            $lookupClientIds[] = $site->client_id;
                        }
                    }
                }

                if (!empty($lookupClientIds)) {
                    foreach ($lookupClientIds as $cid) {
                        if (isset($clientsMap[$cid]))
                            $clientNames[] = $clientsMap[$cid];
                    }
                }

                $supervisorSites[$assign->user_id] = [
                    'site' => array_values(array_unique($siteNames)),
                    'client' => array_values(array_unique($clientNames))
                ];
            }
        }

        // Fetch missing users (shown in report but no attendance)
        $missingUsers = Users::where('users.company_id', $user->company_id)
            ->rightjoin('site_assign', 'users.id', '=', 'site_assign.user_id')
            ->where('users.showUser', 1)
            ->whereNotIn('users.id', $userIds)
            ->when($user->role_id == 2, fn($query) => $query->whereNotIn('users.role_id', [1, 2, 4])->whereIn('users.id', $site_UserIds))
            ->when($user->role_id == 1, fn($query) => $query->whereIn('users.role_id', [2, 3, 7]))
            ->when($user->role_id == 7, fn($query) => $query->whereIn('users.id', $site_UserIds))
            ->orderBy('site_assign.client_name')
            ->orderBy('site_assign.site_name')
            ->select('users.id', 'users.name')
            ->get();

        foreach ($missingUsers as $value) {
            $data[$value->id] = [];
            $names[$value->id] = [$value->name];
        }

        $allUserIdsForWeekoff = array_keys($names);
        $weekoffs = SiteAssign::whereIn('user_id', $allUserIdsForWeekoff)
            ->groupBy(['user_id', 'weekoff'])
            ->get()
            ->mapToGroups(fn($item) => [$item['user_id'] => $item['weekoff']])
            ->toArray();

        // Fetch company details
        $companyData = CompanyDetails::where('id', $user->company_id)->first();
        $companyName = $companyData->name;
        $companyId = $user->company_id;

        // Fetch attendance count data
        $attendCount = $allData->groupBy('date')->map(fn($group) => $group->pluck('user_id')->unique()->values()->toArray())->toArray();

        ksort($attendCount);

        // dump('attendance site', $attendSites);
        // dump($sites , "sites");


        //  dump('All guard Attendance Report Controller' , $supervisorSites);

        // Generate report
        if (count($data) > 0) {
            ActivityLog::create([
                'date_time' => date('Y-m-d H:i:s'),
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'type' => "All Employee Attendance Report",
                'message' => "All employee attendance report downloaded by " . $user->name,
            ]);

            $params = [
                'data' => $data,
                'weekoffs' => $weekoffs,
                'names' => $names,
                'attendCount' => $attendCount,
                'date' => $date,
                'daysCount' => $daysCount,
                'startDatee' => $startDatee,
                'companyName' => $companyName,
                'fromdate' => $request->fromdate,
                'todate' => $request->todate,
                'currentDate' => $currentDate,
                'dateFormat' => $dateFormat,
                'sites' => $sites,
                'hours' => $hours,
                'subType' => $subType,
                'attendanceSubType' => $request->attendanceSubType,
                'supervisorSites' => $supervisorSites,
                'fileType' => $request->xlsx,
                'attendSites' => $attendSites,
                'generatedOn' => $this->generatedOn
            ];

            if ($request->xlsx == "xlsx") {
                return $this->excel->download(new AllGuardAttendance(...array_values($params)), $subType . '.xlsx');
            } else {
                // dump('in pdf' , $subType. '.pdf');
                if ($startDate == $endDate) {
                    // $customPaper = 'A4';
                    $customPaper = array(0, 0, 595, 1000); // A4 size in points

                } else if ($subType === 'Employee Attendance Report With Hours' || $subType === 'Employee Attendance Report With site') {
                    $customPaper = array(0, 0, 500, 4000);
                } else {
                    // dump('in else');
                    $customPaper = array(0, 0, 500, 2000);
                }
                $pdf = PDF::loadView('AttendanceReport.allGuardReportWithSite', array_merge($params, [
                    'guard' => $guard,
                    'geofences' => $geofences,
                    'client' => $client,
                    'type' => $subType,
                    'fileType' => $request->xlsx
                ]))->setPaper($customPaper, 'landscape');
                return $pdf->stream($subType . '.pdf');
            }
        }

        return redirect()->back()->with('alert', 'Records not found');
    }



    public function downloadAbsentReport(Request $request)
    {
        // dd($request->all());
        $user = session('user');
        if ($request->subType == "Absent") {
            $subType = 'Absent Attendance Report';
        } else {
            $subType = $request->subType;
        }

        $startDate = $request->fromdate;
        $endDate = $request->todate;
        // dump($startDate , $endDate);

        $reportMonth = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));
        $company = CompanyDetails::where('id', $user->company_id)->first();
        $companyName = $company->name;
        $geofences = $request->geofences;
        $client = $request->client;
        $guard = $request->guard;
        // $type = "absentReport";
        $flag = $request->xlsx;

        $userIds = SiteAssign::where('company_id', $user->company_id)
            // ->where('client_id', $request->client)
            ->pluck('user_id')
            ->toArray();

        $attend = Attendance::where('company_id', $user->company_id)
            ->whereIn('user_id', $userIds)
            ->whereBetween('dateFormat', [$startDate, $endDate])
            ->select('user_id')
            ->distinct()
            ->pluck('user_id')
            ->toArray();

        // Consolidate role-based site user identification
        $site_UserIds = collect();
        if ($user->role_id == 2) {
            $siteIds = SiteAssign::where('user_id', $user->id)
                ->pluck('site_id')
                ->map(fn($item) => is_string($item) ? json_decode($item, true) : $item)
                ->flatten()->unique()->values();

            $site_UserIds_query = SiteAssign::where('company_id', $user->company_id)->whereIn('site_id', $siteIds);
            if ($client == 'all' || ($client !== 'all' && $geofences == 'all')) {
                $site_UserIds = $site_UserIds_query->pluck('user_id');
            } else if ($client !== 'all' && $geofences !== 'all') {
                $site_UserIds = $site_UserIds_query->where('site_id', $geofences)->pluck('user_id');
            }
        } else if ($user->role_id == 7) {
            $siteAssigned = SiteAssign::where('user_id', $user->id)->first();
            $client_ids = json_decode($siteAssigned['site_id'], true) ?? [];

            if ($client == 'all') {
                $userIds = SiteAssign::whereIn('client_id', $client_ids)->where('role_id', 3)->pluck('user_id')->toArray();
            } else {
                $userIds = SiteAssign::where('client_id', $client)->where('role_id', 3)->pluck('user_id')->toArray();
            }
            $site_UserIds = collect($userIds);
        } else {
            // Role 1 or default
            $site_UserIds = SiteAssign::where('company_id', $user->company_id)->pluck('user_id');
        }

        // Identify those who have attendance
        $attend = Attendance::where('company_id', $user->company_id)
            ->whereIn('user_id', $site_UserIds)
            ->whereBetween('dateFormat', [$startDate, $endDate])
            ->select('user_id')
            ->distinct()
            ->pluck('user_id')
            ->toArray();

        if (empty($attend) && $site_UserIds->isEmpty()) {
            return 'No records found';
        }

        // Final query to find absent users
        $query = Users::where('users.company_id', $user->company_id)
            ->leftJoin('site_assign as site', 'users.id', '=', 'site.user_id')
            ->whereIn('users.id', $site_UserIds)
            ->whereNotIn('users.id', $attend)
            ->where('users.showUser', 1)
            ->select('users.*', 'site.site_name as site_name', 'site.client_name as client_name')
            ->orderBy('site.client_name', 'ASC')
            ->orderBy('users.name', 'ASC');

        if ($geofences != 'all' && $geofences != null) {
            $query->where('site.site_id', $geofences);
        }
        if ($guard != 'all' && $guard != null) {
            $query->where('users.id', $guard);
        }

        $data = $query->get();
        if ($data->isEmpty()) {
            return 'No records found';
        }

        // $attend = Attendance::where('company_id', $user->company_id)->whereBetween('dateFormat', [$startDate, $endDate])->select('user_id')->distinct()->pluck('user_id')->toArray();
        // $data  = Users::where('users.company_id', $user->company_id)
        //     ->leftjoin('site_assign as site', 'users.id', '=', 'site.user_id')->whereNotIn('users.id', $attend)
        //     ->whereNotIn('users.role_id', [1, 4])->select('users.*', 'site.site_name as site_name')->where('users.showUser', 1)
        //     ->orderBy('users.name', 'ASC')->get();
        // dd($data);
        if (count($data) > 0) {
            if ($request->xlsx == "xlsx") {
                ActivityLog::create([
                    'date_time' => date('Y-m-d H:i:s'),
                    'company_id' => $user->company_id,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'type' => "Absent Report",
                    'message' => "Absent report downloaded by " . $user->name,
                ]);
                return $this->excel->download(new AbsentExport($data, $companyName, $reportMonth, $flag, $subType, $this->generatedOn), 'Absent-Report.xlsx');
            } else {
                // return $this->excel->download(new GuardAbsentExport($data, $site, $companyName, $reportMonth, $guard, $daysCount, $type), 'forgot_to_mark_exit_report.pdf');
                $pdf = PDF::loadView(
                    'AttendanceReport.absentReport',
                    [
                        'data' => $data,
                        'companyName' => $companyName,
                        'dateRange' => $reportMonth,
                        'flag' => $flag,
                        'subType' => $subType,
                        'generatedOn' => $this->generatedOn
                    ]
                )->setPaper('a4', 'portrait');
                return $pdf->stream('Absent-Report.pdf');
                // return $pdf->download('Absent-Report.pdf');
            }
        } else {
            return redirect()->back()->with('alert', 'Records not found');
        }
    }





    public function downloadLateReport(Request $request)
    {
        // dd('here');

        $user = session('user');
        $fromdate = $request->input('fromdate');
        $todate = $request->input('todate');
        $attendanceSubType = $request->input('attendanceSubType');
        $subType = $request->input('subType');
        $guard = $request->input('guard');
        $geofences = $request->input('geofences');

        $fromdate = $request->input('fromdate');
        $todate = $request->input('todate');
        $attendanceSubType = $request->input('attendanceSubType');
        $subType = $request->input('subType');
        $guard = $request->input('guard');
        $geofences = $request->input('geofences');
        $client = $request->input('client');

        $clientName = $request->clientName;
        $siteName = $request->siteName;

        // Dump the values for debugging
        // dump('From Date:', $fromdate);
        // dump('To Date:', $todate);
        // dump('Attendance SubType:', $attendanceSubType);
        // dump('Sub Type:', $subType);
        // dump('Guard:', $guard);
        // dump('client:', $client);
        // dd('Geofences:', $geofences);

        if ($request->subType == "Late") {
            $subType = 'Late Attendance Report';
        } else {
            $subType = $request->subType;
        }

        $startDate = $request->fromdate;
        $endDate = $request->todate;
        $reportMonth = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));
        $company = CompanyDetails::where('id', $user->company_id)->first();
        $companyName = $company->name;
        $flag = $request->xlsx;


        $attendanceQuery = Attendance::where('attendance.company_id', $user->company_id)
            ->leftjoin('site_assign as site', 'attendance.user_id', '=', 'site.user_id')
            ->whereBetween('attendance.dateFormat', [$startDate, $endDate])
            ->whereNotNull('attendance.lateTime')
            ->where('site.role_id', 3)
            ->orderBy('attendance.name')
            ->orderBy('attendance.dateFormat');
        try {

            if ($user->role_id == 7) {
                $siteQuery = SiteAssign::where('company_id', $user->company_id);


                // dump('in else if');
                // Fetch site assignments for the user
                $siteAssigned = SiteAssign::where('user_id', $user->id)->first();

                // $client_ids = json_decode($siteAssigned, true);
                $client_ids = json_decode($siteAssigned['site_id'], true);
                // dd($siteAssigned ,  $client_ids,"site Assigned");

                if ($client == 'all') {
                    $siteArray = SiteDetails::whereIn('client_id', $client_ids)->pluck('id')->toArray();
                    $userIds = SiteAssign::whereIn('client_id', $client_ids)->pluck('user_id')->toArray();
                } else {

                    $siteArray = SiteDetails::where('client_id', $client)->pluck('id')->toArray();
                    $userIds = SiteAssign::where('client_id', $client)->pluck('user_id')->toArray();
                }


                // dd($siteArray , "array");

                $supervisorIds = Users::where([['users.company_id', '=', $user->company_id], ['users.role_id', '=', 2]])
                    ->leftJoin('site_assign as site', 'users.id', '=', 'site.user_id')
                    ->where(function ($query) use ($siteArray) {
                        foreach ($siteArray as $siteId) {
                            $query->orWhereRaw('JSON_CONTAINS(site.site_id, ?)', [json_encode([$siteId])]);
                        }
                    })->pluck('site.user_id')->toArray();


                // dd($userIds, "user ids");

                $userIds = array_merge($userIds, $supervisorIds);

                // dd($site_UserIds, "ids");
                // dump($siteQuery->pluck('user_name')->toArray());
                $siteQuery->when(
                    $geofences !== 'all' && $geofences !== null,
                    fn($query) => $query->where('site_id', $geofences)

                )
                    ->whereIn('user_id', $userIds);

                // dd($siteQuery->pluck('client_id')->toArray());
                // dd($siteQuery->pluck('user_id')->toArray());
                if ($client == 'all') {
                    // Case 1: All clients
                    // dump('cli is all');
                    $data = $attendanceQuery
                        ->whereIn('attendance.user_id', $userIds)
                        ->get();

                    // dd($data->pluck('client_name')->toArray());
                } elseif ($geofences == 'all') {
                    // Case 2: All sites for a specific client
                    $userIds = SiteAssign::where('company_id', $user->company_id)
                        ->where('client_id', $client)
                        ->pluck('user_id');
                    // dump($geofences, "geofences");
                    $data = $attendanceQuery
                        ->whereIn('attendance.user_id', $userIds)
                        ->get();
                } elseif ($guard == 'all') {
                    // Case 3: All guards for a specific site
                    $userIds = SiteAssign::where('company_id', $user->company_id)
                        ->where('site_id', $geofences)
                        ->pluck('user_id');

                    $data = $attendanceQuery
                        ->whereIn('attendance.user_id', $userIds)
                        ->get();
                } elseif ($guard != 'all') {
                    // Case 4: Specific guard for a specific site
                    $data = $attendanceQuery
                        ->where('attendance.user_id', $guard)
                        ->get();
                }
            } else if ($user->role_id == 2) {

                // dump('the role id is 2');
                $siteIds = $user->role_id == 2
                    ? SiteAssign::where('user_id', $user->id)
                        ->pluck('site_id')
                        ->map(function ($item) {
                            return is_string($item) ? json_decode($item, true) : $item;
                        })
                        ->flatten()
                        ->unique()
                        ->values()
                    : collect();
                // dump($siteIds, "siteIds arr");

                $site_UserIds_query = SiteAssign::where('company_id', $user->company_id)->whereIn('site_id', $siteIds);
                // dd($site_UserIds);

                if ($client == 'all') {
                    $site_UserIds = $site_UserIds_query->pluck('user_id');
                    $data = $attendanceQuery->where('attendance.user_id', $site_UserIds)->get();
                } else if ($client !== 'all' && $geofences == 'all') {
                    // dd($client, $geofences, "client");
                    // $attendanceQuery->whereIn('user_id', $site_UserIds_query->pluck('user_id'));
                    $site_UserIds = $site_UserIds_query->pluck('user_id');
                    // dd($site_UserIds , "site userids");

                    $data = $attendanceQuery->whereIn('attendance.user_id', $site_UserIds)->get();

                    //    dd($data , "daata");
                } else if ($client !== 'all' && $geofences !== 'all' && $guard == 'all') {
                    $site_UserIds = $site_UserIds_query->where('site_id', $geofences)->pluck('user_id');
                    $data = $attendanceQuery->whereIn('attendance.user_id', $site_UserIds)->get();
                } elseif ($geofences != 'all' && $guard != 'all') {
                    // Case 3: Single site, single employee
                    $data = $attendanceQuery
                        ->where('attendance.user_id', $guard)
                        ->get();

                    $siteDetails = SiteDetails::find($geofences);
                    $site = $siteDetails ? $siteDetails->site_name : 'Unknown Site';
                }
            } else {
                // Other roles (e.g., role ID != 2)
                if ($client == 'all') {
                    // Case 1: All clients
                    $data = $attendanceQuery
                        ->get();
                } elseif ($geofences == 'all') {
                    // dd('in geogyu' , $guard);
                    // Case 2: All sites for a specific client
                    $userIds = SiteAssign::where('company_id', $user->company_id)
                        ->where('client_id', $client)
                        ->pluck('user_id')->toArray();
                    // dump($geofences, "geofences");
                    // dd('in geofences' , $client ,$userIds );

                    $data = $attendanceQuery
                        ->whereIn('attendance.user_id', $userIds)
                        ->get();
                    // dd($data, $userIds, "data");
                } elseif ($guard == 'all') {
                    // dd($geofences, "geofences");
                    // Case 3: All guards for a specific site
                    $userIds = SiteAssign::where('company_id', $user->company_id)
                        ->where('site_id', $geofences)
                        ->pluck('user_id');

                    $data = $attendanceQuery
                        ->whereIn('attendance.user_id', $userIds)
                        ->get();
                } elseif ($guard != 'all') {
                    // Case 4: Specific guard for a specific site
                    $data = $attendanceQuery
                        ->where('attendance.user_id', $guard)
                        ->get();
                }
            }

            // dd($data , $userIds, "data" );

            if ($data->isEmpty()) {
                return redirect()->back()->with('alert', 'No late attendance records found for the selected criteria');
            }

            ActivityLog::create([
                'date_time' => date('Y-m-d H:i:s'),
                'company_id' => $user->company_id,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'type' => "Late Report",
                'message' => "Late report downloaded by " . $user->name,
            ]);

            if ($request->xlsx == "xlsx") {
                return $this->excel->download(new LateExport(
                    $data,
                    $companyName,
                    $reportMonth,
                    $flag,
                    $subType,
                    $client,
                    $clientName,
                    $siteName,
                    $this->generatedOn
                ), 'Late-Report.xlsx');
            } else {
                $pdf = PDF::loadView('AttendanceReport.lateReport', [
                    'data' => $data,
                    'companyName' => $companyName,
                    'dateRange' => $reportMonth,
                    'flag' => $flag,
                    'subType' => $subType,
                    'client' => $request->client,
                    'clientName' => $clientName,
                    'siteName' => $siteName,
                    'generatedOn' => $this->generatedOn
                ])->setPaper('a4', 'portrait');

                return $pdf->stream('Late-Report.pdf');
            }
        } catch (\Exception $e) {
            // Log the error if needed
            Log::error('Late Report Error: ' . $e->getMessage());
            dd('Late Report Error: ' . $e->getMessage());
            return redirect()->back()->with('alert', 'Error generating report. Please try again after sometime.');
        }
    }

    public function downloadOnSiteReport(Request $request)
    {
        $user = session('user');
        Log::info($user->name . ' view attendance report, User_id: ' . $user->id);

        // Parse dates
        $startDate = date('Y-m-d', strtotime($request->startDate));
        $endDate = date('Y-m-d', strtotime($request->endDate));
        $reportMonth = date('d M Y', strtotime($startDate)) . " to " . date('d M Y', strtotime($endDate));

        // Initialize variables
        $companyId = $user->company_id;
        $roleId = $user->role_id;
        $geofences = $request->geofences;
        $client = $request->client;
        $geofencesNew = ($client == 'all' && $request->geofences == null) ? 'all' : $geofences;

        // Fetch company details
        $company = CompanyDetails::find($companyId);
        $companyName = $company->name;

        // Determine site IDs based on role and client
        $siteQuery = SiteAssign::where('company_id', $companyId);


        $siteInfo = [
            'name' => 'All Sites',
            'client' => [
                'id' => $client,
                'name' => 'All Clients'
            ]
        ];

        if ($client !== 'all' && $geofences == 'all') {
            $clientDetails = ClientDetails::find($client);
            $siteInfo['client']['name'] = $clientDetails->name ?? 'All Client';
            // dump($siteInfo, "siteInfo");
        } else {
            $clientDetails = ClientDetails::find($client);
            $site = SiteAssign::where('site_id', $geofences)->first();
            // dd($site->site_name , "site");
            $siteInfo['client']['name'] = $clientDetails->name ?? 'All Client';
            $siteInfo['name'] = $site->site_name ?? 'All Site';
            //   dump($siteInfo , "info when cli all");
        }


        $siteIds = $user->role_id == 2
            ? SiteAssign::where('user_id', $user->id)
                ->pluck('site_id')
                ->map(function ($item) {
                    return is_string($item) ? json_decode($item, true) : $item;
                })
                ->flatten()
                ->unique()
                ->values()
            : collect();

        if ($roleId === 1) {

            $userIds = SiteAssign::where('client_id', $client)->pluck('user_id')->toArray();
            // dd($client , $userIds , "user ids");
            $siteArray = SiteDetails::where('client_id', $client)->pluck('id')->toArray();
            // dd($siteArray , "siteArray");
            $supervisorIds = Users::where([['users.company_id', '=', $user->company_id], ['users.role_id', '=', 2]])
                ->leftJoin('site_assign as site', 'users.id', '=', 'site.user_id')
                ->where(function ($query) use ($siteArray) {
                    foreach ($siteArray as $siteId) {
                        $query->orWhereRaw('JSON_CONTAINS(site.site_id, ?)', [json_encode([$siteId])]);
                    }
                })->pluck('users.id')->toArray();
            // dump($userIds, $supervisorIds, "ids");

            $userIds = array_merge($userIds, $supervisorIds);

            // Admin role can view all sites, optionally filtered by client
            if ($client !== 'all') {
                $siteQuery->when(
                    $geofences !== 'all' && $geofences !== null,
                    fn($query) => $query->where('site_id', $geofences)

                )
                    ->whereIn('user_id', $userIds);
                // dd($siteQuery->pluck('user_name')->toArray());

            }
        } else if ($user->role_id == 7) {

            // dump('in else if');
            // Fetch site assignments for the user
            $siteAssigned = SiteAssign::where('user_id', $user->id)->first();

            // $client_ids = json_decode($siteAssigned, true);
            $client_ids = json_decode($siteAssigned['site_id'], true);
            // dd($siteAssigned ,  $client_ids,"site Assigned");

            if ($client == 'all') {
                $siteArray = SiteDetails::whereIn('client_id', $client_ids)->pluck('id')->toArray();
                $userIds = SiteAssign::whereIn('client_id', $client_ids)->pluck('user_id')->toArray();
            } else {

                $siteArray = SiteDetails::where('client_id', $client)->pluck('id')->toArray();
                $userIds = SiteAssign::where('client_id', $client)->pluck('user_id')->toArray();
                // dd('not loading' , $siteArray , $userIds);

            }


            // dd($siteArray , "array");

            $supervisorIds = Users::where([['users.company_id', '=', $user->company_id], ['users.role_id', '=', 2]])
                ->leftJoin('site_assign as site', 'users.id', '=', 'site.user_id')
                ->where(function ($query) use ($siteArray) {
                    foreach ($siteArray as $siteId) {
                        $query->orWhereRaw('JSON_CONTAINS(site.site_id, ?)', [json_encode([$siteId])]);
                    }
                })->pluck('site.user_id')->toArray();


            // dd($userIds, "user ids");

            $site_UserIds = array_merge($userIds, $supervisorIds);

            // dump($siteQuery->pluck('user_name')->toArray());
            $siteQuery->when(
                $geofences !== 'all' && $geofences !== null,
                fn($query) => $query->where('site_id', $geofences)

            )
                ->whereIn('user_id', $site_UserIds);

            // dd($siteQuery->pluck('client_id')->toArray());
            // dd($siteQuery->pluck('user_id')->toArray());
        } else {
            // Supervisor role can only view sites assigned to them

            $siteQuery->whereIn('site_id', $siteIds);
            // dump($siteIds, "site ids");
            if ($geofences !== 'all' && $geofences !== null) {
                // dump(10);
                $siteQuery->where('site_id', $geofences);
            }
            ;
        }

        $site_UserIds = $siteQuery
            ->pluck('user_id')
            ->map(fn($item) => is_string($item) ? json_decode($item, true) : $item)
            ->flatten()
            ->unique()
            ->values();

        // Fetch attendance data
        $attendanceQuery = Attendance::where('emergency_attend', 1)
            ->where('company_id', $companyId)
            ->whereBetween('dateFormat', [$startDate, $endDate]);

        if ($roleId === 1 || $roleId === 7) {
            // Admin role attendance filtering
            $attendanceQuery->whereIn('user_id', $site_UserIds);
        } else {
            // Supervisor role attendance filtering
            $attendanceQuery->when(
                $geofences == 'all',
                fn($query) => $query->whereIn('site_id', $siteIds)
            )
                ->when($geofences !== 'all', fn($query) => $query->where('site_id', $geofences));
        }

        $data = $attendanceQuery->get();

        if ($data->isEmpty()) {
            return redirect()->back()->with('alert', 'Records not found');
        }

        // Log activity
        ActivityLog::create([
            'date_time' => date('Y-m-d H:i:s'),
            'company_id' => $companyId,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'type' => "On Site Report",
            'message' => "On site report downloaded by " . $user->name,
        ]);

        // $excelData = 
        // Generate and return the requested file format
        if ($request->xlsx) {
            return $this->excel->download(
                new OnSiteExport(
                    $data,
                    $geofencesNew,
                    $reportMonth,
                    $startDate,
                    $endDate,
                    $companyName,
                    $geofences,
                    $siteInfo['client']['name'],
                    $siteInfo['name'],
                    1,
                    $this->generatedOn
                ),
                'OnSiteAttendance.xlsx'
            );
        } else {
            $pdf = PDF::loadView('AttendanceReport.onSiteAttendanceReport', [
                'data' => $data,
                'dateRange' => $reportMonth,
                'flag' => $request->xlsx,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'companyName' => $companyName,
                'site' => $geofences,
                'geofences' => $geofencesNew,
                'clientName' => $siteInfo['client']['name'],
                'siteName' => $siteInfo['name'],
                'generatedOn' => $this->generatedOn

            ])->setPaper('a4', 'landscape');

            return $pdf->stream('On-Site-Attendance.pdf');
        }
    }

    public function downloadClientVisitReport(Request $request)
    {
        // dd($request->all());
        $user = session('user');
        $startDate = $request->fromDate;
        $endDate = $request->toDate;
        $reportMonth = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));
        $company = CompanyDetails::where('id', $user->company_id)->first();
        $companyName = $company->name;
        //$type = "absentReport";
        $flag = $request->xlsx;
        $allData = json_decode($request->allData, true);
        // dd($allData);
        //if ($allData['client'] == "all") {
        //     $data = ClientVisit::where('company_id', $user->company_id)->whereBetween('date', [$startDate, $endDate])
        //         ->orderBy('date', 'ASC')->get();
        // } else
        if ($allData['geofences'] == 'all') {
            $data = ClientVisit::where('client_id', $allData['client'])->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'ASC')->get();
        } elseif ($allData['guard'] == 'all') {
            $data = ClientVisit::where('site_id', $allData['geofences'])->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'ASC')->get();
        } else {
            $data = ClientVisit::where('user_id', $allData['guard'])->whereBetween('date', [$startDate, $endDate])
                ->orderBy('date', 'ASC')->get();
        }
        // dd($data);
        if (count($data) > 0) {
            if ($request->xlsx == "xlsx") {
                return $this->excel->download(new ClientVisitExport($data, $companyName, $reportMonth, $flag), 'Client-Visit-Report.xlsx');
            } else {
                // return $this->excel->download(new GuardAbsentExport($data, $site, $companyName, $reportMonth, $guard, $daysCount, $type), 'forgot_to_mark_exit_report.pdf');
                $pdf = PDF::loadView('reports.clientVisitReport', ['data' => $data, 'companyName' => $companyName, 'dateRange' => $reportMonth, 'flag' => $flag])->setPaper('a4', 'landscape');
                return $pdf->stream('Client-Visit-Report.pdf');
                // return $pdf->download('Client-Visit-Report.pdf');
            }
        } else {
            return "error";
        }
    }


    public function downloadTourDiaryReport(Request $request)
    {
        // dd($request->all());
        $user = session('user');
        $startDate = $request->fromDate;
        $endDate = $request->toDate;
        $reportMonth = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));
        $company = CompanyDetails::where('id', $user->company_id)->first();
        $companyName = $company->name;
        $clientName = $request->clientName;
        $flagType = $request->flagType;
        // dd($flagType, "flag type");
        // dd($clientName , "cku name");
        $siteName = $request->siteName;
        //$type = "absentReport";
        $flag = $request->xlsx;
        $allData = json_decode($request->allData, true);
        if ($user->role_id == 1) {
            if ($allData['client'] == "all") {
                $userIds = Users::where('company_id', $user->company_id)->pluck('id')->toArray();
            } elseif ($allData['geofences'] == "all") {
                $userIds = SiteAssign::where('client_id', $allData['client'])->pluck('user_id')->toArray();
            } elseif ($allData['guard'] == "all") {
                $userIds = SiteAssign::where('site_id', $allData['geofences'])->pluck('user_id')->toArray();
            } else
                $userIds = [$allData['guard']];
        } elseif ($user->role_id == '7') {

            // dump('in else if');
            // Fetch site assignments for the user
            $siteAssigned = SiteAssign::where('user_id', $user->id)->first();

            // $client_ids = json_decode($siteAssigned, true);
            $client_ids = json_decode($siteAssigned['site_id'], true);
            // dd($siteAssigned ,  $client_ids,"site Assigned");

            if ($allData['client'] == "all") {
                $siteArray = SiteDetails::whereIn('client_id', $client_ids)->pluck('id')->toArray();
                $userIds = SiteAssign::whereIn('client_id', $client_ids)->pluck('user_id')->toArray();
            } elseif ($allData['geofences'] == "all") {
                $userIds = SiteAssign::where('client_id', $client_ids)->pluck('user_id')->toArray();
            } elseif ($allData['guard'] == "all") {
                $userIds = SiteAssign::where('site_id', $allData['geofences'])->pluck('user_id')->toArray();
            } else
                $userIds = [$allData['guard']];
        } else {

            $allData['client'] = null;
            // dump($guard  , $geofences, "guard");

            $siteIds = $user->role_id == 2
                ? SiteAssign::where('user_id', $user->id)
                    ->pluck('site_id')
                    ->map(function ($item) {
                        return is_string($item) ? json_decode($item, true) : $item;
                    })
                    ->flatten()
                    ->unique()
                    ->values()
                : collect();
            // dump($siteIds, "siteIds arr");

            $site_UserIds_query = SiteAssign::where('company_id', $user->company_id)->whereIn('site_id', $siteIds);
            // dd($site_UserIds);
            if ($allData['geofences'] == 'all') {
                $userIds = $site_UserIds_query->pluck('user_id');
            } else if ($allData['geofences'] !== 'all' && $allData['guard'] == 'all') {
                $userIds = $site_UserIds_query->where('site_id', $allData['geofences'])->pluck('user_id');
            } else if ($allData['geofences'] !== 'all' && $allData['guard'] !== 'all') {
                // dd($guard, "guard");
                $userIds = [$allData['guard']];
            }
        }

        // $data = TourDiary::whereIn('user_id', $userIds)->whereBetween('start_time', [$startDate . " 00:00:00", $endDate . " 23:59:59"])
        //     ->orderBy('start_time', 'ASC')->get();


        $data = TourDiary::leftJoin('client_details', 'tour_diary.client_id', '=', 'client_details.id')
            // ->leftJoin('site_assign', function ($join) {
            //     $join->on('tour_diary.user_id', '=', 'site_assign.user_id')
            //         ->whereNull('tour_diary.client_id'); // Fetch only where client_id is missing
            // })
            ->leftJoin('site_assign as site', 'tour_diary.user_id', '=', 'site.user_id')
            ->leftJoin('client_details as site_clients', 'site.client_id', '=', 'site_clients.id')
            ->whereIn('tour_diary.user_id', $userIds)
            ->whereBetween('tour_diary.start_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('tour_diary.start_time', 'ASC')
            ->selectRaw('
            tour_diary.*,
            client_details.id as tour_client_id,
            site.site_name as site_name,
            site_clients.id as site_client_id,
            COALESCE(client_details.name, site_clients.name) as client_name
        ')
            ->get();

        // dd($data , "data");

        // if ($allData['client'] == "all") {
        //     $data = TourDiary::where('company_id', $user->company_id)->whereBetween('start_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
        //         ->orderBy('start_time', 'ASC')->get();
        // } elseif ($allData['geofences'] == 'all') {
        //     $data = TourDiary::where('client_id', $request->client)->whereBetween('start_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
        //         ->orderBy('start_time', 'ASC')->get();
        // } elseif ($allData['guard'] == 'all') {
        //     $data = TourDiary::where('site_id', $request->geofences)->whereBetween('start_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
        //         ->orderBy('start_time', 'ASC')->get();
        // } else {
        //     $data = TourDiary::where('user_id', $request->guard)->whereBetween('start_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
        //         ->orderBy('start_time', 'ASC')->get();
        // }

        // dd($userIds);
        // if ($allData['client'] == "all")
        // if ($allData['geofences'] == 'all') {
        //     $data = TourDiary::where('client_id', $allData['client'])->whereBetween('date', [$startDate, $endDate])
        //         ->orderBy('date', 'ASC')->get();
        // } elseif ($allData['guard'] == 'all') {
        //     $data = TourDiary::where('site_id', $allData['geofences'])->whereBetween('date', [$startDate, $endDate])
        //         ->orderBy('date', 'ASC')->get();
        // } else {
        //     $data = TourDiary::where('user_id', $allData['guard'])->whereBetween('date', [$startDate, $endDate])
        //         ->orderBy('date', 'ASC')->get();
        // }
        if (count($data) > 0) {
            $customPaper = array(0, 0, 500, 1400);
            if ($request->xlsx == "xlsx") {
                return $this->excel->download(new TourDiaryExport(
                    $data,
                    $companyName,
                    $reportMonth,
                    $flag,
                    $allData,
                    $clientName,
                    $siteName,
                    $flagType,
                    $request->subType
                ), $request->subType . '.xlsx');
            } else {
                // return $this->excel->download(new GuardAbsentExport($data, $site, $companyName, $reportMonth, $guard, $daysCount, $type), 'forgot_to_mark_exit_report.pdf');
                $pdf = PDF::loadView(
                    'reports.tourDiaryReport',
                    [
                        'data' => $data,
                        'allData' => $allData,
                        'companyName' => $companyName,
                        'dateRange' => $reportMonth,
                        'flag' => $flag,
                        'clientName' => $clientName,
                        'siteName' => $siteName,
                        'flagType' => $flagType,
                        'subType' => $request->subType

                    ]
                )
                    ->setPaper($customPaper, 'landscape');
                return $pdf->stream($request->subType . 'pdf');
                // return $pdf->download('Client-Visit-Report.pdf');
            }
        } else {
            return "error";
            // return redirect()->back()->with('alert', 'Records not found');
        }
    }



    public function downloadSelfTourDiaryReport(Request $request, )
    {
        // dd($subType , 'data');
        $user = session('user');
        $startDate = $request->fromDate;
        $endDate = $request->toDate;
        $reportMonth = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));
        $client = $requestData['client'] ?? 'all';
        $geofences = $requestData['geofences'] ?? 'all';
        $guard = $requestData['guard'] ?? 'all';
        $clientName = $request->clientName;
        $siteName = $request->siteName;
        $flag = $request->flag;
        $flagType = $request->flagType;

        $allData = json_decode($request->allData, true);

        // dd($allData, "allData");

        // $user_data = Users::where('name', 'DA Ghuge')->first();
        // dd($user_data, "user data");
        $requestData['client'] = null;

        // $userIds = '1626';

        $userIds = $user->id;

        $data = TourDiary::leftJoin('client_details', 'tour_diary.client_id', '=', 'client_details.id')
            ->leftJoin('site_assign as site', 'tour_diary.user_id', '=', 'site.user_id')
            ->leftJoin('client_details as site_clients', 'site.client_id', '=', 'site_clients.id')
            ->where('tour_diary.user_id', $userIds)
            ->whereBetween('tour_diary.start_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('tour_diary.start_time', 'ASC')
            ->selectRaw('
            tour_diary.*,
            client_details.id as tour_client_id,
            site.site_name as site_name,
            site_clients.id as site_client_id,
            COALESCE(client_details.name, site_clients.name) as client_name')
            ->get();


        $company = CompanyDetails::find($user->company_id);
        $companyName = $company->name ?? '';

        $filteredData = collect($requestData)->except([
            '_token',
            'fromDate',
            'toDate',
            'incidencePriority',
            'incidentSubType',
            'visitorSubType',
            'tourSubType',
            'attendanceSubType',
            'tourDate'
        ])->toArray();

        // dd($data ,'data');



        if (count($data) > 0) {
            $customPaper = array(0, 0, 500, 1400);
            if ($request->xlsx == "xlsx") {
                return $this->excel->download(new TourDiaryExport(
                    $data,
                    $companyName,
                    $reportMonth,
                    $flag,
                    $allData,
                    $clientName,
                    $siteName,
                    $flagType,
                    $request->subType
                ), $request->subType . '.xlsx');
            } else {
                // return $this->excel->download(new GuardAbsentExport($data, $site, $companyName, $reportMonth, $guard, $daysCount, $type), 'forgot_to_mark_exit_report.pdf');
                $pdf = PDF::loadView(
                    'reports.tourDiaryReport',
                    [
                        'data' => $data,
                        'allData' => $allData,
                        'companyName' => $companyName,
                        'dateRange' => $reportMonth,
                        'flag' => $flag,
                        'clientName' => $clientName,
                        'siteName' => $siteName,
                        'flagType' => $flagType,
                        'subType' => $request->subType
                    ]
                )
                    ->setPaper($customPaper, 'landscape');
                return $pdf->stream($request->subType . 'pdf');
                // return $pdf->download('Client-Visit-Report.pdf');
            }
        } else {
            return "error";
            // return redirect()->back()->with('alert', 'Records not found');
        }
    }



    public function downloadSuperVisorTourDiaryReport(Request $request)
    {
        $user = session('user');
        $startDate = $request->fromDate;
        $endDate = $request->toDate;
        $reportMonth = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));
        $clientName = $request->clientName;
        $siteName = $request->siteName;
        $flag = $request->flag;
        $flagType = $request->flagType;

        $allData = json_decode($request->allData, true);
        $requestData['client'] = null;

        if ($user->role_id == '7') {
            $siteAssigned = SiteAssign::where('user_id', $user->id)->first();

            $client_ids = json_decode($siteAssigned['site_id'], true);

            $siteArray = SiteDetails::whereIn('client_id', $client_ids)->pluck('id')->toArray();
            // dd($siteArray , "array");

            if ($request->supervisorSelect == 'all') {
                $userIds = Users::where([['users.company_id', '=', $user->company_id], ['users.role_id', '=', 2]])
                    ->leftJoin('site_assign as site', 'users.id', '=', 'site.user_id')
                    ->where(function ($query) use ($siteArray) {
                        foreach ($siteArray as $siteId) {
                            $query->orWhereRaw('JSON_CONTAINS(site.site_id, ?)', [json_encode([$siteId])]);
                        }
                    })->pluck('site.user_id')->toArray();
            } else {

                $userIds = $request->supervisorSelect;
            }
        } else {
            // for role id 1
            if ($request->supervisorSelect == "all") {
                $userIds = SiteAssign::where('company_id', $user->company_id)->where('role_id', 2)->get()->pluck('user_id')->unique();
            } else {
                // dump('here');
                $userIds = $request->supervisorSelect;
            }
        }


        $data = TourDiary::leftJoin('client_details', 'tour_diary.client_id', '=', 'client_details.id')
            ->leftJoin('site_assign as site', 'tour_diary.user_id', '=', 'site.user_id')
            ->leftJoin('client_details as site_clients', 'site.client_id', '=', 'site_clients.id')
            ->when($request->supervisorSelect !== 'all', function ($query) use ($request) {
                return $query->where('tour_diary.user_id', $request->supervisorSelect);
            }, function ($query) use ($userIds) {
                return $query->whereIn('tour_diary.user_id', $userIds);
            })
            ->whereBetween('tour_diary.start_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('tour_diary.start_time', 'ASC')
            ->selectRaw('
        tour_diary.*,
        client_details.id as tour_client_id,
        site.site_name as site_name,
        site_clients.id as site_client_id,
        COALESCE(client_details.name, site_clients.name) as client_name')
            ->get();


        $company = CompanyDetails::find($user->company_id);
        $companyName = $company->name ?? '';

        $filteredData = collect($requestData)->except([
            '_token',
            'fromDate',
            'toDate',
            'incidencePriority',
            'incidentSubType',
            'visitorSubType',
            'tourSubType',
            'attendanceSubType',
            'tourDate'
        ])->toArray();

        // dd($data, 'data');



        if (count($data) > 0) {
            $customPaper = array(0, 0, 500, 1400);
            if ($request->xlsx == "xlsx") {
                return $this->excel->download(new TourDiaryExport(
                    $data,
                    $companyName,
                    $reportMonth,
                    $flag,
                    $allData,
                    $clientName,
                    $siteName,
                    $flagType,
                    $request->subType

                ), $request->subType . '.xlsx');
            } else {
                // return $this->excel->download(new GuardAbsentExport($data, $site, $companyName, $reportMonth, $guard, $daysCount, $type), 'forgot_to_mark_exit_report.pdf');
                $pdf = PDF::loadView(
                    'reports.tourDiaryReport',
                    [
                        'data' => $data,
                        'allData' => $allData,
                        'companyName' => $companyName,
                        'dateRange' => $reportMonth,
                        'flag' => $flag,
                        'clientName' => $clientName,
                        'siteName' => $siteName,
                        'flagType' => $flagType,
                        'subType' => $request->subType
                    ]
                )
                    ->setPaper($customPaper, 'landscape');
                return $pdf->stream($request->subType . 'pdf');
                // return $pdf->download('Client-Visit-Report.pdf');
            }
        } else {
            return "error";
            // return redirect()->back()->with('alert', 'Records not found');
        }
    }




    public function downloadAdminTourDiaryReport(Request $request)
    {
        $user = session('user');
        $startDate = $request->fromDate;
        $endDate = $request->toDate;
        $reportMonth = date('d-m-Y', strtotime($startDate)) . " to " . date('d-m-Y', strtotime($endDate));
        $clientName = $request->clientName;
        $siteName = $request->siteName;
        $flag = $request->flag;
        $flagType = $request->flagType;

        $allData = json_decode($request->allData, true);
        $requestData['client'] = null;
        // dd($request->adminSelect, "admin select");
        if ($request->adminSelect == 'all') {
            // $userIds = Users::where('company_id', $user->company_id)->where('role_id', $user->role_id)->pluck('id')->toArray();
            $userIds = SiteAssign::where('company_id', $user->company_id)->where('role_id', 7)->get()->pluck('user_id')->unique();
            // dd($userIds, "userIds");
        } else {
            $userIds = $request->adminSelect;
        }


        $data = TourDiary::leftJoin('client_details', 'tour_diary.client_id', '=', 'client_details.id')
            ->leftJoin('site_assign as site', 'tour_diary.user_id', '=', 'site.user_id')
            ->leftJoin('client_details as site_clients', 'site.client_id', '=', 'site_clients.id')
            ->when($request->supervisorSelect !== 'all', function ($query) use ($request) {
                return $query->where('tour_diary.user_id', $request->supervisorSelect);
            }, function ($query) use ($userIds) {
                return $query->whereIn('tour_diary.user_id', $userIds);
            })
            ->whereBetween('tour_diary.start_time', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('tour_diary.start_time', 'ASC')
            ->selectRaw('
        tour_diary.*,
        client_details.id as tour_client_id,
        site.site_name as site_name,
        site_clients.id as site_client_id,
        COALESCE(client_details.name, site_clients.name) as client_name')
            ->get();


        $company = CompanyDetails::find($user->company_id);
        $companyName = $company->name ?? '';

        $filteredData = collect($requestData)->except([
            '_token',
            'fromDate',
            'toDate',
            'incidencePriority',
            'incidentSubType',
            'visitorSubType',
            'tourSubType',
            'attendanceSubType',
            'tourDate'
        ])->toArray();

        // dd($data, 'data');



        if (count($data) > 0) {
            $customPaper = array(0, 0, 500, 1400);
            if ($request->xlsx == "xlsx") {
                return $this->excel->download(new TourDiaryExport(
                    $data,
                    $companyName,
                    $reportMonth,
                    $flag,
                    $allData,
                    $clientName,
                    $siteName,
                    $flagType,
                    $request->subType

                ), $request->subType . '.xlsx');
            } else {
                // return $this->excel->download(new GuardAbsentExport($data, $site, $companyName, $reportMonth, $guard, $daysCount, $type), 'forgot_to_mark_exit_report.pdf');
                $pdf = PDF::loadView(
                    'reports.tourDiaryReport',
                    [
                        'data' => $data,
                        'allData' => $allData,
                        'companyName' => $companyName,
                        'dateRange' => $reportMonth,
                        'flag' => $flag,
                        'clientName' => $clientName,
                        'siteName' => $siteName,
                        'flagType' => $flagType,
                        'subType' => $request->subType
                    ]
                )
                    ->setPaper($customPaper, 'landscape');
                return $pdf->stream($request->subType . 'pdf');
                // return $pdf->download('Client-Visit-Report.pdf');
            }
        } else {
            return "error";
            // return redirect()->back()->with('alert', 'Records not found');
        }
    }


    public function downloadPatrollingStatusReport(Request $request)
    {

        // dd('reached in download patrol ');
        // dd($request->all());

        $user = session('user');
        $company_id = $user->company_id;

        $startDate = Carbon::parse($request->fromDate)->startOfDay();
        $endDate = Carbon::parse($request->toDate)->endOfDay();
        $reportMonth = $startDate->format('d-m-Y') . " to " . $endDate->format('d-m-Y');

        $patrolSubType = $request->subType;
        $clientId = $request->client;
        $beatId = $request->geofences;
        $employeeId = $request->guard;

        // dump($patrolSubType, $clientId, $beatId, $employeeId, "ids");
        $allData = json_decode($request->allData, true);

        // --- Build query ---
        $query = PatrolSession::query()
            ->with([
                'user',
                'site',
            ])
            ->where('company_id', $company_id)
            ->whereBetween('started_at', [$startDate, $endDate]);

        // if ($clientId && $clientId !== 'all') {
        //     $query->whereHas('beat.range', function ($q) use ($clientId) {
        //         $q->where('id', $clientId);
        //     });
        // }

        // if ($beatId && $beatId !== 'all') {
        //     $query->where('beat_id', $beatId);
        // }

        // if ($employeeId && $employeeId !== 'all') {
        //     $query->where('user_id', $employeeId);
        // }

        $patrols = $query->orderBy('started_at', 'desc')->get();
        // dd($patrols, $startDate, $endDate, "pat");
        // dd($patrols[15]->site->client_name, "12");

        $company = CompanyDetails::find($user->company_id);
        $companyName = $company->name ?? 'N/A';

        // --- If no records found ---
        if ($patrols->count() === 0) {
            return "error"; // Or redirect back with message
        }

        // --- Handle export ---
        $customPaper = [0, 0, 500, 1400];

        // dd($clientId , "client id");
        if ($request->format == "xlsx") {
            return $this->excel->download(
                new PatrollingStatusExport(
                    $patrols,
                    $companyName,
                    $reportMonth,
                    $patrolSubType,
                    $allData,
                    $clientId,
                    $beatId,
                    $employeeId
                ),
                $patrolSubType . '.xlsx'
            );
        } else {
            // dd('load pdf' , $clientId );
            $pdf = PDF::loadView(
                'reports.patrollingStatusReportDownload',
                [
                    'data' => $patrols,
                    'allData' => $allData,
                    'companyName' => $companyName,
                    'dateRange' => $reportMonth,
                    'subType' => $patrolSubType,
                    'clientName' => $request->clientName,
                    'siteName' => $request->siteName,
                    'clientId' => $clientId,
                    'beatId' => $request->beatId,
                    'guardName' => $request->guardName,
                ]
            )->setPaper($customPaper, 'landscape');

            return $pdf->stream($patrolSubType . '.pdf');
        }
    }


    public function patrollingSummaryDownload(Request $request)
    {
        $user = session('user');
        $companyName = $user->company_name ?? 'N/A';

        $summary = collect(json_decode($request->summary, true));
        $dateRange = $request->dateRange;

        if ($request->format == "xlsx") {
            return $this->excel->download(
                new PatrollingSummaryExport($summary, $companyName, $dateRange),
                "Patrolling_Summary.xlsx"
            );
        }

        $pdf = PDF::loadView('reports.patrollingSummaryReportDownload', [
            'summary' => $summary,
            'companyName' => $companyName,
            'dateRange' => $dateRange
        ]);

        return $pdf->stream("Patrolling_Summary.pdf");
    }

    public function patrollingLogsReportDownload(Request $request)
    {
        $logs = collect(json_decode($request->logs, true));
        $companyName = $request->companyName;
        $dateRange = $request->dateRange;
        $logType = $request->logType;

        if ($request->format === 'xlsx') {
            return $this->excel->download(
                new PatrolLogsExport($logs, $companyName, $dateRange, $logType),
                'Patrol_Logs_Report.xlsx'
            );
        }

        $pdf = PDF::loadView('reports.patrolLogsReportPDF', [
            'logs' => $logs,
            'companyName' => $companyName,
            'dateRange' => $dateRange,
            'logType' => $logType,
        ]);

        return $pdf->stream("Patrol_Logs_Report.pdf");
    }


    /**
     * Handle Patrol Report Export (from Blade via AJAX)
     */
    public function exportPatrolReport(Request $request)
    {

        $v = Validator::make($request->all(), [
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d|after_or_equal:startDate',
            'exportType' => 'required|in:xlsx,pdf',
            'site_id' => 'nullable',
            'client_id' => 'nullable',
            'method' => 'nullable|string',
            'patrolTypes' => 'nullable|string',
            'logTypes' => 'nullable|string',
        ]);

        if ($v->fails()) {
            return response()->json([
                'status' => 'FAILED',
                'message' => $v->errors()->first(),
            ], 422);
        }

        // Normalize filters
        $start = Carbon::createFromFormat('Y-m-d', $request->startDate)->startOfDay();
        $end = Carbon::createFromFormat('Y-m-d', $request->endDate)->endOfDay();
        $siteId = $request->site_id ?? null;
        $clientId = $request->client_id ?? null;
        $method = $request->method ?? 'all';
        $patrolTypes = $request->patrolTypes ?? 'all';
        $logTypes = $request->logTypes ?? 'all';

        // Query sessions
        $sessionsQ = PatrolSession::with(['site.client', 'user', 'media'])
            ->whereBetween('started_at', [$start, $end]);

        if ($siteId && $siteId !== 'all') {
            $sessionsQ->where('site_id', $siteId);
        }

        if ($clientId && $clientId !== 'all') {
            $sessionsQ->whereHas('site', function ($q) use ($clientId) {
                $q->where('client_id', $clientId);
            });
        }

        if ($method !== 'all') {
            $sessionsQ->where('method', $method);
        }

        if ($patrolTypes !== 'all') {
            $sessionsQ->where('type', $patrolTypes);
        }

        $sessions = $sessionsQ->orderBy('started_at')->get();

        // Query logs
        $logsQ = PatrolLog::with(['session.site.client', 'session.user', 'media'])
            ->whereHas('session', function ($q) use ($start, $end, $siteId, $clientId) {
                $q->whereBetween('started_at', [$start, $end]);
                if ($siteId && $siteId !== 'all') {
                    $q->where('site_id', $siteId);
                }
                if ($clientId && $clientId !== 'all') {
                    $q->whereHas('site', function ($qq) use ($clientId) {
                        $qq->where('client_id', $clientId);
                    });
                }
            });

        if ($logTypes !== 'all') {
            $logsQ->where('type', $logTypes);
        }

        $logs = $logsQ->orderBy('created_at')->get();

        // Build metrics (same as API version)
        $metrics = [
            'total_sessions' => $sessions->count(),
            'total_logs' => $logs->count(),
        ];

        // Decide export
        $fileBase = 'Patrol_Report_' . now()->format('Ymd_His');
        $path = "reports/{$fileBase}." . $request->exportType;

        if ($request->exportType === 'xlsx') {
            $export = new \App\Exports\GuardPatrolReportExport($sessions, $logs, [], $metrics);
            $export->export($fileBase)->store('xlsx', storage_path('app/public/reports'));
        } else {
            $pdf = PDF::loadView('reports.guard_patrol_pdf', [
                'sessions' => $sessions,
                'logs' => $logs,
                'metrics' => $metrics,
            ])->setPaper('a4', 'landscape');

            Storage::disk('public')->put($path, $pdf->output());
        }

        return response()->json([
            'status' => 'SUCCESS',
            'filename' => basename($path),
            'fileurl' => url("storage/$path"),
        ]);
    }
}
