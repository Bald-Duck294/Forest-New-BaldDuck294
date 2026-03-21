@include('includes.report-header')
<div class="row stickyTable">
    <div class="col-md-11">
        <table class="table">
            <thead style="min-width: 70px;">
                <tr>
                    <th colspan="1" style="text-align: center;">Organization</th>
                    <th colspan="1" style="text-align: center;">Client</th>
                    <th colspan="1" style="text-align: center;">Site</th>
                    <th colspan="1" style="text-align: center;">Date Range</th>
                    <th colspan="1" style="text-align: center;">Report Type</th>
                    <th colspan="1" style="text-align: center;">Generated On</th>

                </tr>
            </thead>
            <tbody style="min-width: 70px;">

                <tr> @php
                        $site = App\SiteDetails::where('id', $geofences)->first();
                        $companyName = App\CompanyDetails::where('id', $site->company_id)->first();
                        @endphp
                    <td colspan="1" style="text-align: center;">{{$companyName->name}}</td>
                    <td colspan="1" style="text-align: center;"> {{ $site->client_name }} </td>
                    <td colspan="1" style="text-align: center;"> {{ $site->name }}</td>
                    @if(isset($month))
                        @php
                        $months = substr($month, 5);
                        $year = substr($month, 0, -3);
                        $daysCount = cal_days_in_month(CAL_GREGORIAN, $months, $year);
                        $startDate = $year . "-" . $months . "-01";
                        $endDate = $year . "-" . $months . "-" . $daysCount;
                        $sDate = new DateTime($startDate, new DateTimeZone('Asia/Kolkata'));
                        $reportMonth = $sDate->format('M Y');
                        @endphp
                        <h6>Monthly Report - {{$reportMonth}}</h6>
                        @else
                        @php

                        @endphp
                        <td colspan="1" style="text-align: center;">  {{date('d M Y',strtotime($fromDate))}}
                            to {{date('d M Y',strtotime($toDate))}} </td>
                        @endif
                 
                    <td colspan="1" style="text-align: center;">Visitor Summary report</td>
                    <td colspan="1" style="text-align: center;"><?php echo date("d M Y"); ?></td>
                </tr>

            </tbody>
        </table>
    </div>
    <div class="col-md-1" style="text-align: center;margin-top: -10px;">
        <div class="row">
        <form method="get" action='{{ route("downloadVisitorReportCount") }}'  target="_blank">
            <input type="hidden" name="geofences" value={{$geofences}} />
            <input type="hidden" name="fromDate" value={{$fromDate}} />
            <input type="hidden" name="toDate" value={{$toDate}} />
                <div class="col-md-12" style="display: flex;justify-content: center;">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true" style="border: 1px solid grey;padding: 3px 8px;border-radius: 50%;">×</button>
                </div>
                <div class="col-md-12" style="padding: 3px;display: flex;justify-content: center;">
                    <button type="submit" class="btn btn-danger btn-border btn-round" name="xlsx" value="pdf"><i class="la la-download" title="pdf"></i>PDF</button>
                </div>
                <div class="col-md-12" style="padding: 3px;display: flex;justify-content: center;">
                    <button type="submit" class="btn btn-success btn-border btn-round" name="xlsx" value="xlsx"><i class="la la-download" title="excel"></i>Excel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="row">
   
    <div class="col-md-12">
        <table id="empTable" class="table">
            <thead>
                <tr>
                    <th colspan="2" style="border: 1px solid black;text-align:center;background-color:#B8CCE4;">Sr. No.</th>
                    <th colspan="2" style="border: 1px solid black;text-align:center;background-color:#B8CCE4;">Date</th>
                    <th colspan="2" style="border: 1px solid black;text-align:center;background-color:#B8CCE4;">No. of Visitors</th>

                </tr>
            </thead>
            <?php $srNo = 1 ?>
            <tbody>
                @foreach($VisitorDetails as $item)
                
                @php
                $visitors = App\VisitorDetails::where('date',$item->date)->where('site_id',$item->site_id)->count();
                @endphp
                <tr>
                    <td colspan="2" style="text-align: center;">{{$srNo}}</td>
                    <td colspan="2" style="text-align: center;">{{$item->date}}</td>
                    <td colspan="2" style="text-align: center;">
                        <span style="color:#003add;" onclick="visitors('{{$item->date}}','{{$item->site_id}}')">{{$visitors}}</span>
                    </td>
                </tr>
                <?php $srNo = $srNo + 1; ?>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@include('includes.report-footer')