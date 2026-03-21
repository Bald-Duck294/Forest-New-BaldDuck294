@if($_REQUEST['xlsx'] == "xlsx")
<table style="border-collapse:collapse;width:100%;justify-content:center;">
    <tbody>
        <tr>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Organization</th>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Client</th>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Site</th>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Tour Date</th>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Report type</th>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Generated On</th>

        </tr>
        <tr> @php
            $site = App\SiteDetails::where('id', $_REQUEST['geofences'])->first();
            $companyName = App\CompanyDetails::where('id', $site->company_id)->first();
            @endphp
            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">{{$companyName->name}}</td>
            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">{{$site->client_name}}</td>
            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">{{$site->name}}</td>
            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">{{date('d M Y',strtotime($_REQUEST['tourDate']))}}</td>
            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;"> Daily Tour Report</td>
            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;"><?php echo date("d M Y"); ?></td>
        </tr>
        <tr>
            <td colspan="6"></td>
        </tr>
        @foreach($GuardTourLog as $guardTour)
        <tr>
            <td colspan="1" align="center" style="font-weight:bold;background-color:#d6eba9;border:1px solid black;">Tour Name</td>
            <td colspan="5" style="background-color:#d6eba9;border:1px solid black;">{{$guardTour->tour_name}}</td>
        </tr>
        @php
        $countOfRound = json_decode($guardTour->timeArray);
        @endphp
        @foreach($countOfRound as $item)


        <tr>
            @php
            if($userId == 'all'){
            $guardTourLog = App\GuardTourLog::where('tourId',$guardTour->id)->where('tourDate', $tourDate)->where('round',$item->round)->first();
            }else{
            $guardTourLog = App\GuardTourLog::where('tourId',$guardTour->id)->where('date', $tourDate)->where('round',$item->round)->where('guardId',$userId)->first();
            }
            @endphp
            @if($userId != 'all')
            @if($guardTourLog)

            <th colspan="1" align="center" style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;">Round</th>
            <td colspan="1" align="left" style="background-color:#B8CCE4;border: 1px solid black;">{{$item->round}}</td>
            <th colspan="1" align="center" style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;">Allocated Time</th>
            <td colspan="1" style="background-color:#B8CCE4;border: 1px solid black;">{{date("h:i:s a", strtotime($item->time))}}</td>
            <th colspan="1" align="center" style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;">Tour Status</th>
            @if($guardTourLog['endTime'] == null && $guardTourLog['startTime'] == null)
            <td colspan="1" style="background-color:#fd6e6e;border: 1px solid black; color:black;">Not Started</td>
            @elseif(($guardTourLog['endTime'] == null && $guardTourLog['startTime'] != null) || ($guardTourLog['isEnded'] == '1'))
            <td colspan="1" style="background-color:#ffff75;border: 1px solid black;color:black">Not Completed</td>
            @else
            <td colspan="1" style="background-color:#73ff93;border: 1px solid black;color:black">Completed</td>
            @endif
            @endif
            @else
        </tr>
        <tr>
            <th colspan="1" align="center" style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;">Round</th>
            <td colspan="1" align="left" style="background-color:#B8CCE4;border: 1px solid black;">{{$item->round}}</td>
            <th colspan="1" align="center" style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;">Allocated Time</th>
            <td colspan="1" style="background-color:#B8CCE4;border: 1px solid black;">{{date("h:i:s a", strtotime($item->time))}}</td>
            <th colspan="1" align="center" style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;">Tour Status</th>
            @if(isset($guardTourLog['endTime']))
            @if($guardTourLog['endTime'] == null && $guardTourLog['startTime'] == null)
            <td colspan="1" style="background-color:#fd6e6e;border: 1px solid black; color:black;">Not Started</td>
            @elseif(($guardTourLog['endTime'] == null && $guardTourLog['startTime'] != null) || ($guardTourLog['isEnded'] == '1'))
            <td colspan="1" style="background-color:#ffff75;border: 1px solid black;color:black">Not Completed</td>
            @else
            <td colspan="1" style="background-color:#73ff93;border: 1px solid black;color:black">Completed</td>
            @endif
            @elseif(isset($guardTourLog['startTime']))
            @if($guardTourLog['startTime'] == null)
            <td colspan="1" style="background-color:#fd6e6e;border: 1px solid black; color:black;">Not Started</td>
            @elseif(( $guardTourLog['startTime'] != null) || ($guardTourLog['isEnded'] == '1'))
            <td colspan="1" style="background-color:#ffff75;border: 1px solid black;color:black">Not Completed</td>
            @else
            <td colspan="1" style="background-color:#73ff93;border: 1px solid black;color:black">Completed</td>
            @endif
            @endif

            @endif
        </tr>
        @if(isset($guardTourLog))
        <tr>
            <th colspan="1" align="center" style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;">Performed By</th>
            <td colspan="1" style="background-color:#B8CCE4;">{{$guardTourLog->guardName}}</td>
            <th colspan="1" align="center" style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;">Start time</th>
            <td colspan="1" style="background-color:#B8CCE4;">{{date("h:i:s a", strtotime($guardTourLog->startTime))}}</td>
            <th colspan="1" align="center" style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;">End time</th>
            @if(isset($guardTourLog->endTime))
            <td colspan="1" style="background-color:#B8CCE4;border: 1px solid black;">{{date("h:i:s a", strtotime($guardTourLog->endTime))}}</td>
            @else
            <td colspan="1" style="background-color:#B8CCE4;border: 1px solid black;">-</td>

            @endif
        </tr>


        <tr>
            <th colspan="1" align="center" style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;">Sr. No.</th>
            <th colspan="2" style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;">Name of CheckPoint</th>
            <th colspan="1" style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;">Visit Time</th>
            <th colspan="1" align="center" style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;">Remark</th>
            <th colspan="1" align="center" style="background-color: #B8CCE4;border: 1px solid black;font-weight:bold;">Status</th>
        </tr>
        @php
        $checkPoints = App\GuardTourCheckpoints::where('tourId',$guardTour->id)->get();
        $srNo = 1;
        @endphp
        @foreach($checkPoints as $check)
        @php
        $tourLogStatus = App\TourCheckPointStatus::where('tourLogId',$guardTourLog->id)->where('checkpointId',$check->id)->first();
        @endphp
        <tr>
            <td colspan="1" align="center" style="background-color: #EAEAEA;border: 1px solid black;">{{$srNo}}</td>
            <td colspan="2" style="background-color: #EAEAEA;border: 1px solid black;">{{$check->pointName}}</td>
            @if(isset($tourLogStatus))
            <td colspan="1" style="background-color: #EAEAEA;border: 1px solid black;"> {{date("h:i:s a", strtotime($tourLogStatus->time))}}</td>
            <td colspan="1" align="left" style="background-color: #EAEAEA;border: 1px solid black;">{{$tourLogStatus->remark}}</td>
            <td colspan="1" align="center" style="background-color: #EAEAEA;border: 1px solid black;color:black">Checked</td>
            @else
            <td colspan="1" align="center" style="background-color: #EAEAEA;border: 1px solid black;">-</td>
            <td colspan="1" align="center" style="background-color: #EAEAEA;border: 1px solid black;">-</td>
            <td colspan="1" align="center" style="background-color: #EAEAEA;border: 1px solid black;color:black">Missed</td>
            @endif
        </tr><?php $srNo++; ?>
        @endforeach
        @endif
        <tr>
            <td colspan="6"></td>
        </tr>
        @endforeach

        @endforeach
    </tbody>
</table>
@else
<table style="border-collapse:collapse;width:100%;justify-content:center;">
    <tbody>
        <tr>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Organization</th>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Client</th>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Site</th>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Tour Date</th>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Report type</th>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Generated On</th>

        </tr>
        <tr> @php
            $site = App\SiteDetails::where('id', $_REQUEST['geofences'])->first();
            $companyName = App\CompanyDetails::where('id', $site->company_id)->first();
            @endphp
            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">{{$companyName->name}}</td>
            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">{{$site->client_name}}</td>
            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">{{$site->name}}</td>
            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">{{date('d M Y',strtotime($_REQUEST['tourDate']))}}</td>
            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;"> Daily Tour Report</td>
            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;"><?php echo date("d M Y"); ?></td>
        </tr>
        <tr>
            <td colspan="6"></td>
        </tr>
        <tr>
            <td colspan="6"></td>
        </tr>
        <tr>
            <td colspan="6"></td>
        </tr>

        @foreach($GuardTourLog as $guardTour)
        <tr>
            <td colspan="1" style="font-weight:bold;background-color:#d6eba9;border:1px solid black;text-align:center">Tour Name</td>
            <td colspan="5" style="background-color:#d6eba9;border:1px solid black;">{{$guardTour->tour_name}}</td>

        </tr>
        @php
        $countOfRound = json_decode($guardTour->timeArray);

        @endphp
        @foreach($countOfRound as $item)


        @php
        if($userId == 'all'){
        $guardTourLog = App\GuardTourLog::where('tourId',$guardTour->id)->where('tourDate', $tourDate)->where('round',$item->round)->first();
        }else{
        $guardTourLog = App\GuardTourLog::where('tourId',$guardTour->id)->where('tourDate', $tourDate)->where('round',$item->round)->where('guardId',$userId)->first();
        }

        @endphp
        @if($userId != 'all')
        @if($guardTourLog)

        <tr>
            <th colspan="1" align="center" style="background-color: #B8CCE4;border: 1px solid #000;font-weight:bold;text-align:center;">Round</th>
            <td colspan="1" align="left" style="background-color:#B8CCE4;border: 1px solid #000;">{{$item->round}}</td>
            <th colspan="1" align="center" style="background-color: #B8CCE4;border: 1px solid #000;font-weight:bold;text-align:center;">Allocated Time</th>
            <td colspan="1" style="background-color:#B8CCE4;border: 1px solid #000;">{{$item->time}}</td>
            <th colspan="1" align="center" style="background-color: #B8CCE4;border: 1px solid #000;font-weight:bold;text-align:center;">Tour Status</th>
            @if(isset($guardTourLog['endTime']))
            @if($guardTourLog['endTime'] == null && $guardTourLog['startTime'] == null)
            <td colspan="1" style="background-color:#fd6e6e;border: 1px solid black; color:black;">Not Started</td>
            @elseif(($guardTourLog['endTime'] == null && $guardTourLog['startTime'] != null) || ($guardTourLog['isEnded'] == '1'))
            <td colspan="1" style="background-color:#ffff75;border: 1px solid black;color:black">Not Completed</td>
            @else
            <td colspan="1" style="background-color:#73ff93;border: 1px solid black;color:black">Completed</td>
            @endif
            @elseif(isset($guardTourLog['startTime']))
            @if($guardTourLog['startTime'] == null)
            <td colspan="1" style="background-color:#fd6e6e;border: 1px solid black; color:black;">Not Started</td>
            @elseif(( $guardTourLog['startTime'] != null) || ($guardTourLog['isEnded'] == '1'))
            <td colspan="1" style="background-color:#ffff75;border: 1px solid black;color:black">Not Completed</td>
            @else
            <td colspan="1" style="background-color:#73ff93;border: 1px solid black;color:black">Completed</td>
            @endif
            @endif

        </tr>
        @endif
        @else
        <tr>
            <th colspan="1" align="center" style="background-color: #B8CCE4;border: 1px solid #000;font-weight:bold;text-align:center;">Round</th>
            <td colspan="1" align="left" style="background-color:#B8CCE4;border: 1px solid #000;">{{$item->round}}</td>
            <th colspan="1" align="center" style="background-color: #B8CCE4;border: 1px solid #000;font-weight:bold;text-align:center;">Allocated Time</th>
            <td colspan="1" style="background-color:#B8CCE4;border: 1px solid #000;">{{$item->time}}</td>
            <th colspan="1" align="center" style="background-color: #B8CCE4;border: 1px solid #000;font-weight:bold;text-align:center;">Tour Status</th>
            @if(isset($guardTourLog['endTime']))
            @if($guardTourLog['endTime'] == null && $guardTourLog['startTime'] == null)
            <td colspan="1" style="background-color:#fd6e6e;border: 1px solid black; color:black;">Not Started</td>
            @elseif(($guardTourLog['endTime'] == null && $guardTourLog['startTime'] != null) || ($guardTourLog['isEnded'] == '1'))
            <td colspan="1" style="background-color:#ffff75;border: 1px solid black;color:black">Not Completed</td>
            @else
            <td colspan="1" style="background-color:#73ff93;border: 1px solid black;color:black">Completed</td>
            @endif
            @elseif(isset($guardTourLog['startTime']))
            @if($guardTourLog['startTime'] == null)
            <td colspan="1" style="background-color:#fd6e6e;border: 1px solid black; color:black;">Not Started</td>
            @elseif(( $guardTourLog['startTime'] != null) || ($guardTourLog['isEnded'] == '1'))
            <td colspan="1" style="background-color:#ffff75;border: 1px solid black;color:black">Not Completed</td>
            @else
            <td colspan="1" style="background-color:#73ff93;border: 1px solid black;color:black">Completed</td>
            @endif
            @endif
            
        </tr>
        @endif
        @if(isset($guardTourLog))
        <tr>
            <td colspan="1" style="font-weight:bold;background-color:#B8CCE4;text-align:center;border:1px solid black;">Performed By</td>
            <td colspan="1" style="background-color:#B8CCE4;border:1px solid black;">{{$guardTourLog->guardName}}</td>
            <td colspan="1" style="font-weight:bold;background-color:#B8CCE4;text-align:center;border:1px solid black;">Start time</td>
            <td colspan="1" style="background-color:#B8CCE4;">{{date("h:i:s a", strtotime($guardTourLog->startTime))}}</td>
            <td colspan="1" style="font-weight:bold;background-color:#B8CCE4;text-align:center;border:1px solid black;">End time</td>
            @if(isset($guardTourLog->endTime))
            <td colspan="1" style="background-color:#B8CCE4;border:1px solid black;">{{date("h:i:s a", strtotime($guardTourLog->endTime))}}</td>
            @else
            <td colspan="1" style="background-color:#B8CCE4;border:1px solid black;">-</td>
            @endif
        </tr>
        <tr>
            <td colspan="1" align="center" style="font-weight:bold;border:1px solid black;">Sr. No.</td>
            <td colspan="2" align="justify" style="font-weight:bold;border:1px solid black;">Name of CheckPoint</td>
            <td colspan="1" align="center" style="font-weight:bold;border:1px solid black;">Visit Time</td>
            <td colspan="1" align="center" style="font-weight:bold;border:1px solid black;">Remark</td>
            <td colspan="1" align="center" style="font-weight:bold;border:1px solid black;">Status</td>
        </tr>
        @php
        $checkPoints = App\GuardTourCheckpoints::where('tourId',$guardTour->id)->get();
        $srNo = 1;
        @endphp
        @foreach($checkPoints as $check)
        @php
        $tourLogStatus = App\TourCheckPointStatus::where('tourLogId',$guardTourLog->id)->where('checkpointId',$check->id)->first();
        @endphp
        <tr>
            <td colspan="1" align="center" style="border:1px solid black;">{{$srNo}}</td>
            <td colspan="2" align="justify" style="border:1px solid black;">{{$check->pointName}}</td>
            @if(isset($tourLogStatus))
            <td colspan="1" align="center" style="border:1px solid black;">{{date("h:i:s a", strtotime($tourLogStatus->time))}}</td>
            <td colspan="1" align="left" style="border:1px solid black;"> {{$tourLogStatus->remark}}</td>
            <td colspan="1" align="center" style="border:1px solid black;"><span class="badge badge-success">Checked</span></td>
            @else
            <td colspan="1" align="center" style="border:1px solid black;">-</td>
            <td colspan="1" align="center" style="border:1px solid black;">-</td>
            <td colspan="1" align="center" style="border:1px solid black;"><span class="badge badge-warning">Missed</span></td>
            @endif
        </tr><?php $srNo++; ?>
        @endforeach
        @endif
        @endforeach
        @endforeach
    </tbody>
</table>
@endif