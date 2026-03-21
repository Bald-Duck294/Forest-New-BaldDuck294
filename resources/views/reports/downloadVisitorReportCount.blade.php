<table style="border-collapse:collapse;width:100%;justify-content:center;">
    <tbody>
    <tr>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Organization</th>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Client</th>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Site</th>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Date Range</th>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Report Type</th>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Generated On</th>

        </tr>
        <tr> @php
                $site = App\SiteDetails::where('id', $geofences)->first();
                $companyName = App\CompanyDetails::where('id', $site->company_id)->first();
                @endphp
            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">{{$companyName->name}}</td>
            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">{{$site->client_name}}</td>
            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">{{$site->name}}</td>
            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;"> @if(isset($month))
                @php
                $months = substr($month, 5);
                $year = substr($month, 0, -3);
                $daysCount = cal_days_in_month(CAL_GREGORIAN, $months, $year);
                $startDate = $year . "-" . $months . "-01";
                $endDate = $year . "-" . $months . "-" . $daysCount;
                $sDate = new DateTime($startDate, new DateTimeZone('Asia/Kolkata'));
                $reportMonth = $sDate->format('M Y');
                @endphp
                Monthly Report - {{$reportMonth}}
                @else

                {{date('d M Y',strtotime($fromDate))}}
                to {{date('d M Y',strtotime($toDate))}}
                @endif</td>
            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;"> Tour Summary Report</td>
            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;"><?php echo date("d M Y"); ?></td>
        </tr>
        @if($_REQUEST['xlsx'] == 'pdf')
        <tr>
            <td colspan="6"></td>
        </tr>
        <tr>
            <td colspan="6"></td>
        </tr>
        <tr>
            <td colspan="6"></td>
        </tr>
        @else
        <tr>
            <td colspan="6"></td>
        </tr>
        @endif
     
        
       

        <tr>
            <th colspan="2" style="text-align:center;background-color:#B8CCE4;border:1px solid black;">Sr. No.</th>
            <th colspan="2" style="text-align:center;background-color:#B8CCE4;border:1px solid black;">Date</th>
            <th colspan="2" style="text-align:center;background-color:#B8CCE4;border:1px solid black;">No. of Visitors</th>

        </tr>
        <?php $srNo = 1 ?>
        @foreach($VisitorDetails as $item)
        @php
        $visitors = App\VisitorDetails::where('date',$item->date)->where('site_id',$item->site_id)->count();
        @endphp
        <tr>
            <td colspan="2" style="text-align: center;border:1px solid black;">{{$srNo}}</td>
            <td colspan="2" style="text-align: center;border:1px solid black;">{{$item->date}}</td>
            <td colspan="2" style="text-align: center;border:1px solid black;">{{$visitors}}</td>

        </tr>
        <?php $srNo = $srNo + 1; ?>
        @endforeach
    </tbody>
</table>
