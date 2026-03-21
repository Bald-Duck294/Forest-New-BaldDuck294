@include('includes.report-header')

<div class="row">
    <div class="col-md-12">
        <form method="get" action='{{ route("downloadDailyTour") }}'>
            <input type="hidden" name="type" value={{$type}} />
            <input type="hidden" name="geofences" value={{$geofences}} />
            <input type="hidden" name="tourDate" value={{$tourDate}} />
            <!-- <a href="{{route('report.view')}}">
                        <button type="button" style="background-color: #5bc0de;color:#fff; float:right;" class="btn">Back</button>
                    </a>
                    <button type="submit" style="float:right;margin-right:10px;" class="btn btn-primary">Download</button> -->
            <div class="text-right" style="padding: 10px;">
                <button type="submit" class="btn btn-danger btn-border btn-round" name="xlsx" value="pdf"><i class="la la-file-pdf-o" title="pdf"></i></button>
                <button type="submit" class="btn btn-success btn-border btn-round" name="xlsx" value="xlsx"><i class="la la-file-excel-o" title="excel"></i></button>
                <!-- <button type="button" class="btn btn-warning btn-border btn-round" onclick="history.back()"><i class="la la-arrow-circle-o-left" title="back"></i></button></a> -->
            </div>
        </form>
    </div>
    <div class="col-md-12">
        <table id="empTable" class="table">
            @if(isset($GuardTourLog))
            <table id="empTable" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th colspan="10" style="text-align: center; ">
                            @php
                            $site = App\SiteDetails::where('id', $geofences)->first();
                            $companyName = App\CompanyDetails::where('id', $site->company_id)->first();
                            @endphp
                            <h6 style="font-weight:bold;">{{$companyName->name}}</h6>
                        </th>
                    </tr>
                    <tr>
                        <th colspan="3" style="text-align: center; ">
                            <h6 style="font-weight:bold;">Client</h6>
                        </th>
                        <th colspan="7" align="left">
                            @php
                            $site = App\SiteDetails::where('id', $geofences)->first();
                            $clientName = App\ClientDetails::where('id', $site->client_id)->first();
                            @endphp
                            <h6 style="font-weight:bold;">{{$clientName->name}}</h6>
                        </th>
                    </tr>
                    <tr>
                        <th colspan="3" style="text-align: center; ">
                            <h6 style="font-weight:bold;">Site</h6>
                        </th>
                        <th colspan="7" align="left">
                            @php
                            $site = App\SiteDetails::where('id', $geofences)->first();
                            @endphp
                            <h6 style="font-weight:bold;">{{$site->name}}</h6>
                        </th>
                    </tr>
                    <tr>
                        <th colspan="3" style="text-align: center; ">
                            <h6 style="font-weight:bold;">Report Type</h6>
                        </th>
                        <th colspan="7" align="left">
                            <h6 style="font-weight:bold;">Visitor Daily Report</h6>
                        </th>
                    </tr>
                    <tr>
                        <th colspan="3" style="text-align: center; ">
                            <h6 style="font-weight:bold;">Tour Date</h6>
                        </th>
                        <th colspan="2" align="left">

                            <h6 style="font-weight:bold;">{{date('d M Y',strtotime($_REQUEST['tourDate']))}}</h6>
                        </th>
                        <th colspan="3" style="text-align: center; ">
                            <h6 style="font-weight:bold;">Tour Date</h6>
                        </th>
                        <th colspan="2" align="left">

                            <h6 style="font-weight:bold;">{{date('d M Y')}}</h6>
                        </th>
                    </tr>

                    <tr>
                        <td colspan="8"></td>
                    </tr>
                </thead>

                <tbody>
                    @foreach($GuardTourLog as $item)

                    @php
                    $timeArray = json_decode($item->timeArray, true);

                    if($userId == null){
                    $tourLog = App\GuardTourLog::where('tourId', $item->id)->where('date', $tourDate)->get();
                    }
                    else{
                    $tourLog = App\GuardTourLog::where('tourId', $item->id)->where('guardId', $userId)->where('date', $tourDate)->get();

                    }
                    $srNo = 1;

                    $dataArray = [];


                    foreach($timeArray as $time){
                    $dataArray[] = (object)[
                    'guardName' => '-',
                    'allocatedTime' => $time['time'],
                    'startTime' => '-',
                    'endTime' => '-',
                    'totalTime' => '-',
                    'tourLogId' => null,
                    'round' => null,

                    ];
                    }

                    $dataCount = 0;

                    if(count($tourLog)>0){
                    foreach($tourLog as $log){

                    $allocatedTime = $dataArray[$dataCount]->allocatedTime;
                    $dataArray[$dataCount] = (object)[
                    'guardName' => $log->guardName,
                    'allocatedTime' => $allocatedTime,
                    'startTime' => $log->startTime,
                    'endTime' => $log->endTime,
                    'totalTime' => $log->totalTimeDisp,
                    'tourLogId' => $log->id,
                    'round' => $log->round,

                    ];
                    $dataCount++;
                    }
                    }
                    @endphp

                    @foreach($dataArray as $val)
                    <tr>
                        <th colspan="2">Tour Name</th>
                        <th colspan="8">{{$item->tour_name}}</th>

                    </tr>
                    <tr>
                        <th colspan="3">Round</th>
                        <th colspan="3">{{$val->round}}</th>
                        <?php $time = json_decode($item->timeArray);
                        foreach ($time as $row) {
                            $time = $row->time;
                        } ?>
                        <th colspan="2">Alloted Time</th>
                        <th colspan="2">{{$time}}</th>

                    </tr>
                    <tr>
                        <th colspan="3">Performed By</th>
                        <th colspan="2">
                            {{$val->guardName}}
                        </th>
                        <th colspan="2">Start Time</th>
                        <th colspan="1">{{$time}}</th>
                        <th colspan="1">End Time</th>
                        <th colspan="1">{{$time}}</th>

                    </tr>
                    <tr>
                        <th colspan="1">Sr. No.</th>
                        <th colspan="4">Name of Checkpoint</th>
                        <th colspan="3">Visit Time</th>
                        <th colspan="2">Status</th>
                    </tr>
                    <tr colspan="10">
                        <td colspan="10"></td>
                    </tr>

                    @endforeach
                    @php
                    $checkpointNames = App\GuardTourCheckpoints::where('tourId', $item->id)->get();
                    @endphp
                    @foreach($tourLog as $row)
                    <tr>
                        <td colspan="1">{{$srNo}}</td>
                        @php
                        $checkpointNames = App\GuardTourCheckpoints::where('tourId', $row->tourId)->get();
                        @endphp
                        @foreach($checkpointNames as $item)
                        <td colspan="4">{{$item->pointName}}</td>
                        @endforeach
                        <td colspan="3">{{$row->startTime}}</td>
                        <td colspan="2">Completed</td>
                    </tr>
                    @endforeach
                    <tr>
                        <th>Sr. No.</th>
                        <th>Performed By</th>
                        <th>Round No.</th>
                        <th>Allocated Time</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Total Time</th>
                        <th>Status</th>
                        @php
                        $checkpointNames = App\GuardTourCheckpoints::where('tourId', $item->id)->get();
                        $checkPointStatus = [];
                        @endphp

                        @foreach($checkpointNames as $checkp)
                        <th>{{$checkp->pointName}}</th>
                        @php
                        $checkPointStatus[] = (object)[
                        "time" => "-",
                        "id" => $checkp->id
                        ];
                        @endphp
                        @endforeach

                    </tr>


                    <!-- @php
                                    $checkpoints = App\TourCheckPointStatus:: where('guardTourId', $item->id)->where('date', $tourDate)->get();
                                    @endphp -->

                    <!-- @foreach($dataArray as $val)
                                
                                    $checks = null;
                                    
                                     @foreach($checkpointNames as $checkp)
                                    @php
                                    $checks[] = (object)[
                                    "time" => "-",
                                    "id" => $checkp->id
                                    ];
                                    @endphp
                                    @endforeach -->
                    <!-- <tr>
                                        <td>{{$srNo}}</td>
                                        <td>{{$val->guardName}}</td>
                                        <td>{{$srNo}}</td>
                                        <td>{{$val->allocatedTime}}</td>
                                        <td>{{$val->startTime}}</td>
                                        <td>{{$val->endTime}}</td>
                                        <td>{{$val->totalTime}}</td>
                                        @if($val->startTime == '-' && $val->endTime == '-')
                                        <td>Not started</td>
                                        @elseif($val->startTime != '-' && $val->endTime == '')
                                        <td>Not completed</td>
                                        @else
                                        <td>completed</td>


                                        @endif

                                        @php
                                        if($val->tourLogId != null){
                                        $checkpoints = App\TourCheckPointStatus:: where('tourLogId', $val->tourLogId)->get();
                                        }
                                        else{
                                        $checkpoints = [];
                                        }
                                        @endphp

                                        @if(count($checkpoints) > 0)
                                        @foreach($checkpoints as $check)
                                        @foreach($checks as $resp)
                                        @php
                                        if($resp->id == $check->checkpointId){
                                        $resp->time = $check->time;
                                        }
                                        @endphp
                                        @endforeach
                                        @endforeach
                                        @foreach($checks as $resp)

                                        <td>{{$resp->time}}</td>
                                        @endforeach

                                        @endif
                                    </tr> 
                                  $srNo++;  


                                    @endforeach-->


                    <!-- <tr>
                                        <td colspan="8"></td>

                                    </tr> -->


                    @endforeach

                </tbody>
            </table>

            @endif
    </div>
</div>

@include('includes.report-footer')