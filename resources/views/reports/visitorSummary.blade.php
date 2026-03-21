<table style="border-collapse:collapse;width:100%;justify-content:center;">
    <tbody>
        <tr>
            <th colspan="2" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Organization</th>
            <th colspan="2" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Client</th>
            <th colspan="2" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Site</th>
            <th colspan="2" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Date Range</th>
            <th colspan="2" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Report Type</th>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Generated On</th>

        </tr>
        <tr>
            @php
            $site = App\SiteDetails::where('id', $_REQUEST['siteId'])->first();
            $companyName = App\CompanyDetails::where('id', $site->company_id)->first();
            @endphp
            <td colspan="2" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">{{$companyName->name}}</td>
            <td colspan="2" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">{{$site->client_name}}</td>
            <td colspan="2" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">{{$site->name}}</td>
            <td colspan="2" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">  {{date('d M Y',strtotime($_REQUEST['date']))}}</td>
            <td colspan="2" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">Visitor Summary Report</td>
            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;"><?php echo date("d M Y"); ?></td>
        </tr>
        @if($_REQUEST['xlsx'] == 'pdf')
        <tr>
            <td colspan="11"></td>
        </tr>
        <tr>
            <td colspan="11"></td>
        </tr>
        <tr>
            <td colspan="11"></td>
        </tr>
        @else
        <tr>
            <td colspan="11"></td>
        </tr>
        @endif
      
        <tr>
            <th style="text-align:center;background-color:#B8CCE4;font-weight:bold;border:1px solid black;">Sr. No.</th>
            <th style="text-align:center;background-color:#B8CCE4;font-weight:bold;border:1px solid black;">Visitor Name</th>
            <th style="text-align:center;background-color:#B8CCE4;font-weight:bold;border:1px solid black;">Contact No.</th>
            <th style="text-align:center;background-color:#B8CCE4;font-weight:bold;border:1px solid black;">Whom to Meet</th>
            <th style="text-align:center;background-color:#B8CCE4;font-weight:bold;border:1px solid black;">Purpose of Visit</th>
            <th style="text-align:center;background-color:#B8CCE4;font-weight:bold;border:1px solid black;">Address</th>
            <th style="text-align:center;background-color:#B8CCE4;font-weight:bold;border:1px solid black;">Remarks</th>
            <th style="text-align:center;background-color:#B8CCE4;font-weight:bold;border:1px solid black;">Date</th>
            <th style="text-align:center;background-color:#B8CCE4;font-weight:bold;border:1px solid black;">Entry Time</th>
            <th style="text-align:center;background-color:#B8CCE4;font-weight:bold;border:1px solid black;">Exit Time</th>
            <th style="text-align:center;background-color:#B8CCE4;font-weight:bold;border:1px solid black;">Total Time Spent</th>

        </tr>
        <?php $srNo = 1 ?>
        @foreach($VisitorDetails as $item)
        @php
        $guardName = App\User::where('id', $item->guard_id)->get();
        @endphp
        <tr>
            <td style="text-align: center;border:1px solid black;">{{$srNo}}</td>

            <td style="text-align: center;border:1px solid black;">{{$item->name}}</td>
            <td style="text-align: center;border:1px solid black;">{{$item->mobile}}</td>
            <td style="text-align: center;border:1px solid black;">{{$item->personToMeet}}</td>
            <td style="text-align: center;border:1px solid black;">{{$item->purpose}}</td>
            <td style="text-align: center;border:1px solid black;">{{$item->address}}</td>
            <td style="text-align: center;border:1px solid black;">{{$item->remark}}</td>
            <td style="text-align: center;border:1px solid black;">{{$item->date}}</td>
            <td style="text-align: center;border:1px solid black;">{{$item->entry_time}}</td>
            <td style="text-align: center;border:1px solid black;">{{$item->exit_time}}</td>
            <td style="text-align: center;border:1px solid black;">{{$item->duration}}</td>

        </tr>
        <?php $srNo = $srNo + 1; ?>
        @endforeach
    </tbody>
</table>