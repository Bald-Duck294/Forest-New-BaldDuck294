<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayrollComponent;
use App\Attendance;
use App\Models\Deduction;
use App\Users;
use App\SiteAssign;
use App\Leave;
use Illuminate\Support\Facades\Storage;
use App\ActivityLog;
// use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTime;
use PDF;
use Yajra\DataTables\Facades\DataTables as FacadesDataTables;

class PayrollController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     //
    // }

    // /**
    //  * Show the form for creating a new resource.
    //  */
    // public function create()
    // {
    //     //
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  */
    // public function store(Request $request)
    // {
    //     //
    // }

    // /**
    //  * Display the specified resource.
    //  */
    // public function show(Attendance $attendance)
    // {
    //     //
    // }

    // /**
    //  * Show the form for editing the specified resource.
    //  */
    // public function edit(Attendance $attendance)
    // {
    //     //
    // }

    // /**
    //  * Update the specified resource in storage.
    //  */
    // public function update(Request $request, Attendance $attendance)
    // {
    //     //
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    // public function destroy(Attendance $attendance)
    // {
    //     //
    // }


    private $folder = "admin.payroll.";

    public function index()
    {
        return View('admin.payroll.index', [
            'get_data' => route('payroll.getData'),
        ]);
    }

    public function getData()
    {
        return View('admin.payroll.content', [
            'add_new' => "/",
            'getDataTable' => route('payroll.getDataTable'),
            'payroll_url' => route("payroll.payrollExportPDF"),
            'payslip_url' => route("payroll.payslipExportPDF"),
            'single_payslip_url' => route("payroll.singlePayslipExportPDF"),
        ]);
    }

    public function getDataTable(Request $request)
    {
        // dd($request->salary);
        $monthYear = $request['date'];
        $monthDate = explode('-', $request['date']);

        $data1 = "-01";

        $fromDate = $monthYear . $data1;

        $date = Carbon::create($monthDate[0], $monthDate[1], 1);

        $daysInMonth = $date->daysInMonth;

        $endDate = $monthDate[0] . '-' . $monthDate[1] . '-' . $daysInMonth;
        // dd($fromDate, $endDate);

        $deduction_amount = Deduction::sum("amount");

        //dd($request->all());  
        //dd($payroll);
        $weekoffDates = [];

        // Get the number of days in the month

        // dd($sundays);
        $workingDays = 0;
        $date = $fromDate . ' - ' . $endDate;
        // dd($date);
        $request['date'] = $date;

        // dd($request->all());
        $payroll = $this->payroll($request);
        $totalDays = 0;
        $user = session('user');

        $components = PayrollComponent::where('company_id', $user->company_id)->sum('amount');
        // dd($components);

        // dd($payroll[0]->siteAssign->weekoff);
        return FacadesDataTables::of($payroll)

            ->addIndexColumn()
            ->addColumn('employee', function ($data) {
                return "<div class='row'><div class='col-md-6 col-lg-6 my-auto'><b class='mb-0'>" . $data->name . "</b><p class='mb-2' title='" . $data->gen_id . "'><small><i class='la la-at'></i>" . $data->gen_id . "</small></p></div><div class='col-md-4 col-lg-4'><small class='text-muted float-right'></small></div></div>";
            })
            ->addColumn('basic_salary', function ($data) {
                return number_format($data->salary, 2);
            })
            ->addColumn('gross', function ($data) use ($components) {
                // dd($data->salary);
                $gross = $data->salary + $data->salary * $components / 100;
                return number_format($gross, 2);
            })
            ->addColumn('deduction', function ($data) use ($deduction_amount) {
                return number_format($deduction_amount, 2);
            })
            ->addColumn('leaves', function ($data) use ($monthDate, $fromDate, $endDate, $daysInMonth) {

                $leaveAmount = 0;
                $workingDays = count($data->attendances);

                $numDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthDate[1], $monthDate[0]);
                $absentDays = $numDaysInMonth - $workingDays;
                $leaveList = Leave::where(function ($q) use ($fromDate, $endDate) {
                    $q->whereBetween('fromDate', [$fromDate, $endDate]);
                    $q->orWhereBetween('toDate', [$fromDate, $endDate]);
                })->where('user_id', $data->id)->where('status', 'Approved')->where('is_paid', 1)->get();
                // dd($leaveList);

                // foreach ($data->leaves as $ov) {
                //     $amount += ($ov->rate_amount * $ov->hour) / 60;
                // }
                // dd($data);
                $date1 = Carbon::parse($fromDate);
                $date2 = Carbon::parse($endDate);
                $totalLeavesInMonth = 0;
                foreach ($leaveList as $leave) {
                    $startDate = Carbon::parse($leave->fromDate);
                    $toDate = Carbon::parse($leave->toDate);

                    $start = $startDate->greaterThan($date1) ? $startDate : $date1;
                    $end = $toDate->lessThan($date2) ? $toDate : $date2;

                    $daysMonth = $start->diffInDays($end) + 1;
                    $totalLeavesInMonth += $daysMonth;
                }

                $absentDays -= $totalLeavesInMonth;

                foreach ($data->leaves as $item) {
                    if ($item->duration == 'Half Day') {
                        //dd($item);
                        // $leaveAmount = $leaveAmount + $data->salary / $daysInMonth * 0.5;
                        // dd($leaveAmount);
                        $absentDays = $absentDays - 0.5;
                    } else {
                    }
                }

                $weekoffDates = [];
                $numDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthDate[1], $monthDate[0]);
                if ($data->siteAssign && $data->siteAssign != null) {
                    $weekoff = json_decode($data->siteAssign->weekoff);
                } else {
                    $weekoff = [];
                }



                for ($day = 1; $day <= $numDaysInMonth; $day++) {
                    //Create a DateTime object for the current day
                    $date = new DateTime($monthDate[0] . "-" . $monthDate[1] . "-" . $day);
                    // dd($date);
                    // Check if the day is Sunday (0)
                    if ($weekoff && in_array($date->format('l'), $weekoff)) {
                        $weekoffDates[] = $date->format('Y-m-d');
                    }
                }

                // $user = session('user');
                $attendCount = Attendance::whereIn('dateFormat', $weekoffDates)->where('user_id', $data->id)->groupBy('dateFormat')->get()->count();
                // dd($attendCount);
                $totalWeekOff = count($weekoffDates) - $attendCount;

                $absentDays -= $totalWeekOff;
                // if ($data->id == 90) {
                //     dd($absentDays);
                // }

                $leaveAmount = $leaveAmount + $data->salary / $daysInMonth * $absentDays;
                $data->calculated_leave_amount = $leaveAmount;
                return number_format($leaveAmount, 2);
            })
            ->addColumn('cash_advance', function ($data) {
                // dd($data->cashAdvances->sum('rate_amount'));
                return number_format($data->cashAdvances->sum('rate_amount'), 2);
            })
            ->addColumn('overtime', function ($data) {
                $amount = 0;
                foreach ($data->overtimes as $ov) {
                    $amount += ($ov->rate_amount * $ov->hour) / 60;
                }

                return number_format($amount, 2);
            })

            ->addColumn('net_pay', function ($data) use ($deduction_amount, $workingDays, $monthDate, $totalDays, $components, $daysInMonth) {

                $leaveAmount = $data->calculated_leave_amount ?? 0;
                $paidDays = 0;
                $workingDays = count($data->attendances);
                $total_overtime_amount = 0;
                $count = 0;
                foreach ($data->overtimes as $ov) {
                    $total_overtime_amount += ($ov->rate_amount * $ov->hour) / 60;
                }
                // dd()
                foreach ($data->leaves as $item) {
                    if ($item->duration == 'Half Day') {
                        //dd($item);
                        //$leaveAmount  =  $leaveAmount + $data->salary / $daysInMonth * 0.5;
                        $index = array_search($item->fromDate, array_column($data->attendances->toArray(), 'date'));
                        // dd($index);
                        if ($index !== false) {
                            // if ($item->is_paid === 0) {
                            //     $workingDays = $workingDays - 0.5;
                            // }
                            $paidDays = $paidDays - 0.5;
                        } else {
                            $paidDays = $paidDays + 0.5;
                        }
                        // dd($paidDays);
                    } else {
                        $paidDays += 1;
                    }
                    //    dd($paidDays,$diffInDays);
                }
                //dd($leaveAmount);]
                $weekoffDates = [];
                $numDaysInMonth = cal_days_in_month(CAL_GREGORIAN, $monthDate[1], $monthDate[0]);
                if ($data->siteAssign && $data->siteAssign != null) {
                    $weekoff = json_decode($data->siteAssign->weekoff);
                } else {
                    $weekoff = [];
                }

                for ($day = 1; $day <= $numDaysInMonth; $day++) {
                    //Create a DateTime object for the current day
                    $date = new DateTime($monthDate[0] . "-" . $monthDate[1] . "-" . $day);
                    // dd($date);
                    // Check if the day is Sunday (0)
                    if ($weekoff && in_array($date->format('l'), $weekoff)) {
                        $weekoffDates[] = $date->format('Y-m-d');
                    }
                }

                // $user = session('user');
                $attendCount = Attendance::whereIn('dateFormat', $weekoffDates)->where('user_id', $data->id)->groupBy('dateFormat')->get()->count();

                // dd($attendCount);
                $totalWeekOff = count($weekoffDates) - $attendCount;
                if ($workingDays == 0) {
                    $totalWeekOff = 0;
                }
                // dd($workingDays);
                $totalDays = $workingDays + $totalWeekOff + $paidDays;
                $this->totalDays = $totalDays;

                $daysSalary = $data->salary / $daysInMonth;
                $totalSalary = $daysSalary * $totalDays + $data->salary * $components / 100;
                $total_deduction = $deduction_amount + $data->cashAdvances->sum('rate_amount') + $leaveAmount;


                $amount = ($totalSalary + $total_overtime_amount) - $total_deduction;


                if ($totalDays == 0) {
                    $amount = 0;
                }
                if ($amount <= 0 && $totalDays > 0) {
                    return "<b class='text-danger'>Rs." . number_format($amount, 2) . "</b>";
                }

                // dd($workingDays);
                return "<b>Rs." . number_format($amount, 2) . "</b>";
            })
            ->addColumn('working_days', function ($data) use ($totalDays) {
                // dd($totalDays);
                return $this->totalDays;
            })
            ->rawColumns(['employee', 'basic_salary', 'gross', 'working_days', 'deduction', 'cash_advance', 'net_pay', 'overtime', 'lwp'])
            ->toJson();
        // dd($workingDays);
    }


    public function pay(Request $request)
    {
        $payrolls = $this->payroll($request);
        $user = session('user');
        $components = PayrollComponent::where('company_id', $user->company_id)->sum('amount');

        $dateArray = explode(' ', $request->date);
        // dd($dateArray[0]);
        $day = explode('-', $dateArray[0]);
        $deductions = Deduction::sum("amount");
        return view("admin.payroll.export.payrollView")->with('payrolls', $payrolls)->with('date', $request->date)
            ->with('year', $day[0])->with('month', $day[1])->with('deduction_amount', $deductions)->with('component_per', $components);
        //dd($day);
        // $pdf = PDF::loadView("admin.payroll.export.payroll", [
        //     'payrolls' => $payrolls,
        //     'date' => $request->date,
        //     'year' => $day[0],
        //     'month' => $day[1],
        //     'deduction_amount' => Deduction::sum("amount"),
        //     'component_per' => $components
        // ]); 
    }

    public function payrollExportPDF(Request $request)
    {
        $payrolls = $this->payroll($request);
        $user = session('user');
        $components = PayrollComponent::where('company_id', $user->company_id)->sum('amount');

        $dateArray = explode(' ', $request->date);
        // dd($dateArray[0]);
        $day = explode('-', $dateArray[0]);
        //dd($day);
        $pdf = PDF::loadView("admin.payroll.export.payroll", [
            'payrolls' => $payrolls,
            'date' => $request->date,
            'year' => $day[0],
            'month' => $day[1],
            'deduction_amount' => Deduction::sum("amount"),
            'component_per' => $components
        ]);

        /*
        return View("admin.payroll.export.payroll",[
            'payrolls'=> $payrolls,
            'date'=> $request->date,
            'deduction_amount' => Deduction::sum("amount")
        ]);
        */
        $fileName = "payroll-" . date("d-M-Y") . "-" . time() . '.pdf';

        return $pdf->stream($fileName);

        // return $pdf->download($fileName);
    }

    public function singlepayslip(Request $request)
    {
        // dd($request->all());
        $payslips = $this->payroll($request);

        // dd($payslips);
        if ($request->api) {
            $user = Users::find($request->user_id);
        } else {
            $user = session('user');
        }
        $components = PayrollComponent::where('company_id', $user->company_id)->sum('amount');
        // dd($payslips);

        $dateArray = explode(' ', $request->date);
        // dd($dateArray[0]);
        $day = explode('-', $dateArray[0]);
        // dd($date);
        $payComponent = PayrollComponent::where('company_id', $user->company_id)->get();
        // dd( $components);
        if (count($payslips[0]->attendances) > 0) {
            $deductions = Deduction::where('company_id', $user->company_id)->sum("amount");
        } else {
            $deductions = 0.00;
        }
        return view("admin.payroll.export.payslipView")->with('payComponent', $payComponent)->with('payrolls', $payslips)->with('date', $request->date)
            ->with('year', $day[0])->with('month', $day[1])->with('deduction_amount', $deductions)->with('component_per', $components)->with('single', true)
            ->with('attendCount', count($payslips[0]->attendances))->with('user_id', $request->user_id);
    }

    public function singlepayslipExportPDF(Request $request)
    {
        $payslips = $this->payroll($request);

        // dd($payslips);
        if ($request->api) {
            $user = Users::find($request->user_id);
        } else {
            $user = session('user');
        }
        $components = PayrollComponent::where('company_id', $user->company_id)->sum('amount');
        // dd($payslips);

        $dateArray = explode(' ', $request->date);
        // dd($dateArray[0]);
        $day = explode('-', $dateArray[0]);
        // dd($date);
        $payComponent = PayrollComponent::where('company_id', $user->company_id)->get();
        // dd( $components);
        if (count($payslips[0]->attendances) > 0) {
            $deductions = Deduction::where('company_id', $user->company_id)->sum("amount");
        } else {
            $deductions = 0.00;
        }
        $pdf = PDF::loadView("admin.payroll.export.payslip", [
            'payComponent' => $payComponent,
            'payrolls' => $payslips,
            'date' => $request->date,
            'year' => $day[0],
            'month' => $day[1],
            'deduction_amount' => $deductions,
            'component_per' => $components,
            'single' => true,
            'attendCount' => count($payslips[0]->attendances)
        ])->setPaper('a4', 'portrait');


        $fileName = "payslip-" . date("d-M-Y") . "-" . time() . '.pdf';
        if ($request->api) {
            \Storage::disk('pdf')->put('payslip.pdf', $pdf->output());
            return response()->json([
                'message' => "Excel generated successfully",
                //'data2' => $data,
                "time" => "Time" . time(),
                'status' => 'SUCCESS',
                "success" => true,
                'filename' => $fileName,
                'fileurl' => 'https://fsmdev.pugarch.in/storage/payslip.pdf',
                'code' => 200,
            ], 200);
        } else {

            return $pdf->stream($fileName);
            // return $pdf->download($fileName);
        }
    }

    public function payslip(Request $request)
    {
        // dd($request);
        $payslips = $this->payroll($request);

        $user = session('user');

        $components = PayrollComponent::where('company_id', $user->company_id)->sum('amount');
        // dd($request->date);

        $dateArray = explode(' ', $request->date);
        // dd($dateArray[0]);
        $day = explode('-', $dateArray[0]);
        // dd($date);
        $payComponent = PayrollComponent::where('company_id', $user->company_id)->get();
        // if (count($payslips[0]->attendances) > 0) {
        $deductions = Deduction::where('company_id', $user->company_id)->sum("amount");
        // } else {
        //     $deductions = 0.00;
        // }
        // $customPaper = array(0, 0, 500, 1200);
        // $pdf = PDF::loadView("admin.payroll.export.payslip", [
        //     'payComponent' => $payComponent,
        //     'payrolls' => $payslips,
        //     'date' => $request->date,
        //     'year' => $day[0],
        //     'month' => $day[1],
        //     'deduction_amount' => $deductions,
        //     'component_per' => $components,
        //     'single' => false,
        //     'attendCount' => count($payslips[0]->attendances)
        // ]);

        return view("admin.payroll.export.payslipView")->with('payComponent', $payComponent)->with('payrolls', $payslips)->with('date', $request->date)
            ->with('year', $day[0])->with('month', $day[1])->with('deduction_amount', $deductions)->with('component_per', $components)->with('single', false)
            ->with('attendCount', count($payslips[0]->attendances));
    }

    public function payslipExportPDF(Request $request)
    {
        // $monthYear = $request['date'];
        // $monthDate = explode('-', $request['date']);

        // $data1 = "-01";

        // $fromDate =  $monthYear . $data1;

        // $date = Carbon::create($monthDate[0], $monthDate[1], 1);

        // $daysInMonth = $date->daysInMonth;

        // $endDate = $monthDate[0] . '-' . $monthDate[1] . '-' . $daysInMonth;

        // $date = $fromDate . ' - ' . $endDate;
        // // dd($date);
        // $request['date'] = $date;
        // dd($request);
        $payslips = $this->payroll($request);

        $user = session('user');

        $components = PayrollComponent::where('company_id', $user->company_id)->sum('amount');
        // dd($request->date);

        $dateArray = explode(' ', $request->date);
        // dd($dateArray[0]);
        $day = explode('-', $dateArray[0]);
        // dd($date);
        $payComponent = PayrollComponent::where('company_id', $user->company_id)->get();
        // if (count($payslips[0]->attendances) > 0) {
        $deductions = Deduction::where('company_id', $user->company_id)->sum("amount");
        // } else {
        //     $deductions = 0.00;
        // }
        $customPaper = array(0, 0, 500, 1200);
        $pdf = PDF::loadView("admin.payroll.export.payslip", [
            'payComponent' => $payComponent,
            'payrolls' => $payslips,
            'date' => $request->date,
            'year' => $day[0],
            'month' => $day[1],
            'deduction_amount' => $deductions,
            'component_per' => $components,
            'single' => false,
            'attendCount' => count($payslips[0]->attendances)
        ])->setPaper($customPaper, 'landscape');
        /*return View("admin.payroll.export.payslip",[
            'payrolls'=> $payrolls,
            'date'=> $request->date,
            'deduction_amount'=> Deduction::sum("amount"),
        ]);
        */
        $fileName = "payslip-" . date("d-M-Y") . "-" . time() . '.pdf';

        return $pdf->stream($fileName);

        // return $pdf->download($fileName);
    }




    private function payroll($request)
    {
        //   dd($request->all());
        $date = explode(' - ', $request->date);
        $start_date = date("Y-m-d", strtotime($date[0]));
        $end_date = date("Y-m-d", strtotime($date[1]));
        // dd($start_date, $end_date);
        // $attendances = Attendance::whereIn("dateFormate", [weekoffDates])->();
        // dd($attendances);
        // $empIds = array_unique($attendances);
        // $leaveData = Leave::where(function($r) use ($start_date, $end_date){

        //     $r->whereBetween("fromDate", [$start_date, $end_date])->orWhereBetween("toDate", [$start_date, $end_date]);

        //   })->where("is_paid", 1)->where("status","Approved")->where('user_id',)->count();
        //    dd($leaveData);

        //   foreach($leaves as $item){
        //     $fromDate =Carbon::parse($item->fromDate);
        //     $toDate =Carbon::parse($item->toDate);
        //     // dd($fromDate,$toDate);
        //     // $fromDate = $item->fromDate;
        //     // $toDate = $item->toDate;
        //     $diffInDays= $fromDate->diffInDays($toDate);
        //     // dd($diffInDays);
        //   }  
        //   dd($leaves);
        // "cashAdvances" => function ($q) use ($start_date, $end_date) {
        //     $q->whereBetween("date", [$start_date, $end_date]);
        // },
        // "overtimes" => function ($q) use ($start_date, $end_date) {
        //     $q->whereBetween("date", [$start_date, $end_date]);
        // },
        // "leaves" => function ($q) use ($start_date, $end_date) {
        //     $q->where(function ($r) use ($start_date, $end_date) {
        //         $r->whereBetween("fromDate", [$start_date, $end_date])->orWhereBetween("toDate", [$start_date, $end_date]);
        //     })->where(function ($r) {
        //         $r->where("is_paid", 1)->orWhere('duration', 'Half Day');
        //         // $r;
        //     })->where("status", "Approved");
        // }
        $user = session('user');
        if ($request->user_id) {
            $payslips = Users::where('id', $request->user_id)->with([

                "siteAssign"
                // =>
                //      function ($q) use ($start_date, $end_date) {
                //     $q->whereBetween("dateFormat", [$start_date, $end_date]);
                // $q->select('id', 'date');
                // $q->distinct();
                // }
                ,
                "attendances" => function ($q) use ($start_date, $end_date) {
                    $q->whereBetween("dateFormat", [$start_date, $end_date]);
                    // $q->select('id', 'date');
                    // $q->distinct();
                },
                "cashAdvances" => function ($q) use ($start_date, $end_date) {
                    $q->whereBetween("date", [$start_date, $end_date]);
                },
                "overtimes" => function ($q) use ($start_date, $end_date) {
                    $q->whereBetween("date", [$start_date, $end_date]);
                },
                "leaves" => function ($q) use ($start_date, $end_date) {
                    $q->where(function ($r) use ($start_date, $end_date) {
                        $r->whereBetween("fromDate", [$start_date, $end_date]);
                        $r->orWhereBetween("toDate", [$start_date, $end_date]);
                    })->where(function ($r) {
                        $r->where("is_paid", 0);
                        //$r;
                    })->where("status", "Approved");
                }

            ])->get();
        } else {
            $site_users = [];
            if ($user->role_id == 2) {
                $site_assign = SiteAssign::where('user_id', $user->id)->first();
                $siteArray = [];
                if ($site_assign) {
                    $siteArray = json_decode($site_assign->site_id, true);
                    $site_users = SiteAssign::whereIn('site_id', $siteArray)->pluck('user_id')->toArray();
                }
            }
            $payslips = Users::when($user->role_id == 2, function ($q) use ($site_users) {
                return $q->whereIn('id', $site_users);
            })->where('company_id', $user->company_id)->with([

                "siteAssign",
                "attendances" => function ($q) use ($start_date, $end_date) {
                    $q->whereBetween("dateFormat", [$start_date, $end_date]);
                    // $q->select('id', 'date');
                    // $q->distinct();
                },
                "cashAdvances" => function ($q) use ($start_date, $end_date) {
                    $q->whereBetween("date", [$start_date, $end_date]);
                },
                "overtimes" => function ($q) use ($start_date, $end_date) {
                    $q->whereBetween("date", [$start_date, $end_date]);
                },
                "leaves" => function ($q) use ($start_date, $end_date) {
                    $q->where(function ($r) use ($start_date, $end_date) {
                        $r->whereBetween("fromDate", [$start_date, $end_date]);
                        $r->orWhereBetween("toDate", [$start_date, $end_date]);
                    })->where(function ($r) {
                        $r->where("is_paid", 0);
                        //$r;
                    })->where("status", "Approved");
                }

            ])->get();
        }
        // dd($payslips);
        // $leaves = Leave::with(["leaves" => function ($q) use ($start_date, $end_date) {
        //     $q->whereBetween("date", [$start_date, $end_date]);
        // }])->pluck('user_id')->toArray();

        return $payslips;
    }
}
