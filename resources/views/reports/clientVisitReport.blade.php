<table style="border-collapse:collapse;width:100%;justify-content:center;">
    <tbody>
        <tr>
            <th colspan="3" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Organization</th>
            <th colspan="2" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Date Range</th>
            <th colspan="2" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Report type</th>
            <th colspan="2" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Generated On</th>
        </tr>
        <tr>
            <td colspan="3" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">{{$companyName}} @if($clientName != '') / {{$clientName->name}} @else / N/A @endif</td>
            <td colspan="3" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">{{$dateRange}}</td>
            <td colspan="3" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">Client Visit Report</td>
            <td colspan="3" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">{{date('d M Y')}}</td>
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
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Sr. No</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Date</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Visited By</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Site</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Client Name</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Contact Person</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Contact</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Email</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Address</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Location</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Remark</th>
            <th style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;text-align:center;">Next Meet Date</th>
        </tr>

        <?php $srNo = 1 ?>
        @foreach($data as $item)
        <tr>
            <td style="background-color: #EAEAEA;border: 1px solid black;text-align:center;">{{$srNo++}}</td>
            <td style="background-color: #EAEAEA;border: 1px solid black;">{{date('d-m-Y h:i a', strtotime($item->datetime))}}</td>
            <td style="background-color: #EAEAEA;border: 1px solid black;">{{$item->user_name}}</td>
            @if($item->site_id)
            <td style="background-color: #EAEAEA;border: 1px solid black;">{{$item->site->name}}</td>
            @else
            <td style="background-color: #EAEAEA;border: 1px solid black;">NA</td>
            @endif
            <td style="background-color: #EAEAEA;border: 1px solid black;">{{$item->client_name}}</td>
            <td style="background-color: #EAEAEA;border: 1px solid black;">{{$item->person_met}}</td>
            <td style="background-color: #EAEAEA;border: 1px solid black;">{{$item->person_contact}}</td>
            <td style="background-color: #EAEAEA;border: 1px solid black;">{{$item->person_email}}</td>
            <td style="background-color: #EAEAEA;border: 1px solid black;">{{$item->address}}</td>
            <?php $location = json_decode($item->location); ?>
            <td align="center"><a href="{{'https://maps.google.com/?q='.$location->lat . ',' .$location->lng}}" target="_blank">Location</a> </td>
            <td style="background-color: #EAEAEA;border: 1px solid black;">{{$item->remark}}</td>
            @if($item->nextmeetdatetime)
            <td style="background-color: #EAEAEA;border: 1px solid black;">{{date('d-m-Y h:i a', strtotime($item->nextmeetdatetime))}}</td>
            @else
            <td style="background-color: #EAEAEA;border: 1px solid black;">NA</td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>