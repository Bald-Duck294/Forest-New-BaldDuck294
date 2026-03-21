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
            $site = App\SiteDetails::where('id', $_REQUEST['geofences'])->first();
            $companyName = App\CompanyDetails::where('id', $site->company_id)->first();
            @endphp

            <td colspan="2" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">{{$companyName->name}} </td>
            <td colspan="2" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">{{$site->client_name}}</td>
            <td colspan="2" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">{{$site->name}}</td>
            <td colspan="2" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;"> @php
                $fromDate = $_REQUEST['fromDate'];
                $toDate = $_REQUEST['toDate'];
                @endphp
                {{date('d M Y',strtotime($fromDate))}}
                to {{date('d M Y',strtotime($toDate))}}
            </td>
            <td colspan="2" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;"> Visitor Report</td>
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
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Sr. No.</th>
            <!-- <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;">Guard Name</th> -->
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Visitor Name</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Contact No.</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Whom to Meet</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Purpose of Visit</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Address</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Remark</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Entry Date</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Entry Time</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Exit Time</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Total Time Spent</th>

        </tr>


        <?php $srNo = 1 ?>


        @foreach($VisitorDetails as $item)
        @php
        $guardName = App\User::where('id', $item->guard_id)->get();
        @endphp

        <tr>

            <td style="background-color: #EAEAEA;border: 1px solid black;" align="center">{{$srNo}}</td>
            <!-- @foreach($guardName as $row)
            <td style="background-color: #EAEAEA;border: 1px solid black;;">{{$row->name}}</td>
            @endforeach -->
            <td style="background-color: #EAEAEA;border: 1px solid black;">{{$item->name}}</td>
            <td style="background-color: #EAEAEA;border: 1px solid black;">{{$item->mobile}}</td>
            <td style="background-color: #EAEAEA;border: 1px solid black;">{{$item->personToMeet}}</td>
            <td style="background-color: #EAEAEA;border: 1px solid black;">{{$item->purpose}}</td>
            <td style="background-color: #EAEAEA;border: 1px solid black;">{{$item->address}}</td>
            <td style="background-color: #EAEAEA;border: 1px solid black;">{{$item->remark}}</td>
            <td style="background-color: #EAEAEA;border: 1px solid black;">{{$item->date}}</td>
            <td style="background-color: #EAEAEA;border: 1px solid black;">{{$item->entry_time}}</td>
            <td style="background-color: #EAEAEA;border: 1px solid black;">{{$item->exit_time}}</td>
            <td style="background-color: #EAEAEA;border: 1px solid black;">{{$item->duration}}</td>

        </tr>
        <?php $srNo = $srNo + 1; ?>
        @endforeach
    </tbody>

</table>
<style>
    th {
        page-break-after: always;
        word-wrap: break-word;
    }

    td {
        page-break-after: always;
        word-wrap: break-word;
    }

    table {
        border-collapse: collapse;
    }
</style>