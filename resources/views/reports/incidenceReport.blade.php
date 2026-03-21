@include('includes.header')
<table style="border-collapse:collapse;width:100%;justify-content:center;">
    <tbody>
        <tr>
            <th colspan="3"
                style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">
                Organization</th>
            {{-- <th colspan="2"
                style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">
                Client</th> --}}
            <th colspan="2"
                style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">
                Site</th>
            <th colspan="2"
                style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">
                Date Range</th>
            <th colspan="2"
                style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">
                Report type</th>
            <th colspan="2"
                style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">
                Generated On</th>

        </tr>
        <tr>
            @php
                $user = session('user');
                // dd($user->company_id);
                $site = App\SiteAssign::where('company_id', $user->company_id)->first();
                // dd($site);
                $companyName = App\CompanyDetails::where('id', $user->company_id)->first();
            @endphp

            <td colspan="3" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">
                {{$companyName->name}} </td>
            {{-- <td colspan="2"
                style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;"> @php
                $clientName = App\SiteAssign::where('client_id', $site->client_id)->first();
                @endphp
                {{$clientName->name}}
            </td> --}}
            <td colspan="2" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">
                {{$siteName}}</td>
            <td colspan="2" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">
                @if(isset($_REQUEST['month']) && $_REQUEST['month'] != '/')
                    @php
                        $months = substr($_REQUEST['month'], 5);
                        $year = substr($_REQUEST['month'], 0, -3);
                        $daysCount = cal_days_in_month(CAL_GREGORIAN, $months, $year);
                        $startDate = $year . "-" . $months . "-01";
                        $endDate = $year . "-" . $months . "-" . $daysCount;
                        $sDate = new DateTime($startDate, new DateTimeZone('Asia/Kolkata'));
                        $reportMonth = $sDate->format('M Y');
                    @endphp
                    Monthly Report - {{$reportMonth}}
                @else
                    @php
                        $fromDate = date('d M Y', strtotime($_REQUEST['fromDate']));
                        $toDate = date('d M Y', strtotime($_REQUEST['toDate']));
                    @endphp
                    {{$fromDate}}
                    to {{$toDate}}
                @endif
            </td>
            <td colspan="2" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">
                Incidence Report</td>
            <td colspan="2" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">
                <?php echo date("d M Y"); ?></td>
        </tr>
        @if($_REQUEST['xlsx'] == 'pdf')
            <tr>
                <td colspan="13"></td>
            </tr>
            <tr>
                <td colspan="13"></td>
            </tr>
            <tr>
                <td colspan="13"></td>
            </tr>
        @else
            <tr>
                <td colspan="13"></td>
            </tr>

        @endif
        <tr>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Sr. No.
            </th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Date</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Time</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Priority
            </th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Reported
                By</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Incidence
                Type</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">CheckList
            </th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Employee
                Remark</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Supervisor
                Remark</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Supervisor
                Remark Date Time</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Admin
                Remark</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Admin
                Remark Date Time</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Status
            </th>


        </tr>

        <?php $srNo = 1;
;
        ?>
        @foreach($IncidenceDetails as $item)
            <tr>
                <td style="background-color: #EAEAEA;border: 1px solid black;text-align:center;">{{$srNo}}</td>
                <td style="background-color: #EAEAEA;border: 1px solid black;">{{$item->date}}</td>
                <td style="background-color: #EAEAEA;border: 1px solid black;">{{$item->time}}</td>
                <td style="background-color: #EAEAEA;border: 1px solid black;">{{$item->priority}}</td>
                <td style="background-color: #EAEAEA;border: 1px solid black;">{{$item->guard_name}}</td>
                <td style="background-color: #EAEAEA;border: 1px solid black;">{{$item->type}}</td>
                @php
                    $checkListArray = [];
                    $checkList = null;
                    if ($item->checkList != null) {
                        $checkListArray = json_decode($item->checkList, true);
                        if (count($checkListArray) > 0) {
                            foreach ($checkListArray as $key => $value) {
                                # code...
                                if ($key != count($checkListArray) - 1) {
                                    $checkList = $checkList . $value . ', ';
                                } else {
                                    $checkList = $checkList . $value;
                                }
                            }

                            // $actions = str_slice($actions, str);
                        }
                    }
                @endphp
                <td style="background-color: #EAEAEA;border: 1px solid black;">{{$checkList}}</td>
                <td style="background-color: #EAEAEA;border: 1px solid black;">{{$item->remark}}</td>
                <td style="background-color: #EAEAEA;border: 1px solid black;">{{ $item->supervisorRemark }}</td>
                @if($item->supervisorActionDateTime != '')
                    <td style="background-color: #EAEAEA;border: 1px solid black;">
                        {{ date('d M Y g:i a', strtotime($item->supervisorActionDateTime))}}</td>
                @else
                    <td style="background-color: #EAEAEA;border: 1px solid black;text-align:center;">-</td>
                @endif
                <td style="background-color: #EAEAEA;border: 1px solid black;">{{ $item->adminRemark }}</td>

                @if($item->adminActionDateTime != null)
                    <td style="background-color: #EAEAEA;border: 1px solid black;">
                        {{ date('d M Y g:i a', strtotime($item->adminActionDateTime))}}</td>
                @else
                    <td style="background-color: #EAEAEA;border: 1px solid black;text-align:center;">-</td>
                @endif


                <td style="background-color: #EAEAEA;border: 1px solid black;">{{ $item->status }}</td>

            </tr>
            <?php    $srNo = $srNo + 1; ?>
        @endforeach
    </tbody>
</table>
<style>
    th {
        page-break-after: always;
        word-wrap: normal;
    }

    td {
        page-break-after: always;
        word-wrap: normal;
    }

    table {
        border-collapse: collapse;
    }
</style>