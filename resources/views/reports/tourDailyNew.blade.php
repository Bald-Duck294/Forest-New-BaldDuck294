@include('includes.report-header')
<div class="row stickyTable">
    <div class="col-md-11">
        <table class="table">
            <thead style="min-width: 70px;">
                <tr>
                    <th colspan="3" style="text-align: center;">Organization</th>
                    <th colspan="3" style="text-align: center;">Client</th>
                    <th colspan="3" style="text-align: center;">Site</th>
                    <th colspan="3" style="text-align: center;">Tour Date</th>
                    <th colspan="3" style="text-align: center;">Report Type</th>
                    <th colspan="3" style="text-align: center;">Generated On</th>

                </tr>
            </thead>
            <tbody style="min-width: 70px;">
                @php
                $site = App\SiteDetails::where('id', $geofences)->first();
                $companyName = App\CompanyDetails::where('id', $site->company_id)->first();
                @endphp
                <tr>
                    <td colspan="3" style="text-align: center;">{{ $companyName->name }}</td>
                    <td colspan="3" style="text-align: center;"> @php
                        $clientName = App\ClientDetails::where('id', $site->client_id)->first();
                        @endphp
                        {{$clientName->name}}
                    </td>
                    <td colspan="3" style="text-align: center;"> {{$site->name}}</td>
                    <td colspan="3" style="text-align: center;"> {{date('d M Y',strtotime($tourDate))}}</td>
                    <td colspan="3" style="text-align: center;"> Daily Tour Report</td>
                    <td colspan="3" style="text-align: center;"><?php echo date("d M Y"); ?></td>
                </tr>

            </tbody>
        </table>
    </div>
    <div class="col-md-1" style="text-align: center;margin-top: -10px;">
        <div class="row">
            <form method="get" action='{{ route("downloadDailyTour") }}' target="_blank">
                <input type="hidden" name="geofences" value={{$geofences}} id="geofences" />
                <input type="hidden" name="tourDate" value={{$tourDate}} id="tourDate" />
                <input type="hidden" name="userId" value={{$userId}} id="userId" />
                <input type="hidden" name="userId" value={{$userId}} id="userId" />
                <input type="hidden" name="subtype" value={{$subtype}} id="subtype" />
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
    <div class="col-md-12" style="overflow: scroll;height:70vh;">
        <table class="table">
            <tbody>

                @foreach($GuardTourLog as $guardTour)
                <tr>
                    <td colspan="2" style="font-weight:bold;background-color:#d6eba9;border:1px solid black;text-align:center">Tour Name</td>
                    <td colspan="8" style="background-color:#d6eba9;border:1px solid black;font-weight:bold;">{{ucwords($guardTour->tour_name)}}</td>

                </tr>
                @php
                $countOfRound = json_decode($guardTour->timeArray);

                @endphp
                @foreach($countOfRound as $item)


                @php
                if($userId == 'all'){
                $guardTourLog = App\GuardTourLog::where('tourId',$guardTour->id)->where('tourDate', $tourDate)->where('round',$item->round)->first();
                }else{
                $guardTourLog = App\GuardTourLog::where('tourId',$guardTour->id)->where('date', $tourDate)->where('round',$item->round)->where('guardId',$userId)->first();
                }

                @endphp
                @if($userId != 'all')
                @if($guardTourLog)

                <tr>
                    <th colspan="2" align="center" style="background-color: #B8CCE4;border: 1px solid #000;font-weight:bold;text-align:center;">Round</th>
                    <td colspan="1" align="left" style="background-color:#B8CCE4;border: 1px solid #000;">{{$item->round}}</td>
                    <th colspan="2" align="center" style="background-color: #B8CCE4;border: 1px solid #000;font-weight:bold;text-align:center;">Allocated Time</th>
                    <td colspan="1" style="background-color:#B8CCE4;border: 1px solid #000;">{{$item->time}}</td>
                    <th colspan="2" align="center" style="background-color: #B8CCE4;border: 1px solid #000;font-weight:bold;text-align:center;">Tour Status</th>
                    @if($guardTourLog['endTime'] == null && $guardTourLog['startTime'] == null)
                    <td colspan="2" style="background-color:#fd6e6e; border: 1px solid #000;">Not Started</td>
                    @elseif($guardTourLog['endTime'] == null && $guardTourLog['startTime'] != null || $guardTourLog['isEnded'] == 1 )
                    <td colspan="2" style="background-color:#ffff75;border: 1px solid #000;">Not Completed</td>
                    @else
                    <td colspan="2" style="background-color: #73ff93;border: 1px solid #000;">Completed</td>
                    @endif
                    @endif
                    @else
                </tr>
                <tr>
                    <th colspan="2" align="center" style="background-color: #B8CCE4;border: 1px solid #000;font-weight:bold;text-align:center;">Round</th>
                    <td colspan="1" align="left" style="background-color:#B8CCE4;border: 1px solid #000;">{{$item->round}}</td>
                    <th colspan="2" align="center" style="background-color: #B8CCE4;border: 1px solid #000;font-weight:bold;text-align:center;">Allocated Time</th>
                    <td colspan="1" style="background-color:#B8CCE4;border: 1px solid #000;">{{$item->time}}</td>
                    <th colspan="2" align="center" style="background-color: #B8CCE4;border: 1px solid #000;font-weight:bold;text-align:center;">Tour Status</th>
                    @if(isset($guardTourLog['endTime']))
                    @if($guardTourLog['endTime'] == null && $guardTourLog['startTime'] == null)
                    <td colspan="2" style="background-color:#fd6e6e; border: 1px solid #000;">Not Started</td>
                    @elseif($guardTourLog['endTime'] == null && $guardTourLog['startTime'] != null || $guardTourLog['isEnded'] == 1)
                    <td colspan="2" style="background-color:#ffff75;border: 1px solid #000;">Not Completed</td>
                    @else
                    <td colspan="2" style="background-color: #73ff93;border: 1px solid #000;">Completed</td>
                    @endif

                    @elseif(isset($guardTourLog['startTime']))

                    @if($guardTourLog['startTime'] == null)
                    <td colspan="2" style="background-color:#fd6e6e; border: 1px solid #000;">Not Started</td>
                    @elseif($guardTourLog['startTime'] != null || $guardTourLog['isEnded'] == 1)
                    <td colspan="2" style="background-color:#ffff75;border: 1px solid #000;">Not Completed</td>
                    @else
                    <td colspan="2" style="background-color: #73ff93;border: 1px solid #000;">Completed</td>
                    @endif
                    @endif
                   
                    @endif
                </tr>

                @if(isset($guardTourLog))

                <tr>
                    <td colspan="2" style="font-weight:bold;background-color:#B8CCE4;text-align:center;">Performed By</td>
                    <td colspan="1" style="background-color:#B8CCE4;">{{$guardTourLog->guardName}}</td>
                    <td colspan="2" style="font-weight:bold;background-color:#B8CCE4;text-align:center;">Start time</td>
                    <td colspan="1" style="background-color:#B8CCE4;">{{date("h:i:s a", strtotime($guardTourLog->startTime))}}</td>
                    <td colspan="2" style="font-weight:bold;background-color:#B8CCE4;text-align:center;">End time</td>
                    @if(isset($guardTourLog->endTime))
                    <td colspan="2" style="background-color:#B8CCE4;">{{date("h:i:s a", strtotime($guardTourLog->endTime))}}</td>
                    @else
                    <td colspan="2" style="background-color:#B8CCE4;">-</td>

                    @endif
                </tr>


                <tr>
                    <td colspan="1" align="center" style="font-weight:bold;">Sr. No.</td>
                    <td colspan="5" align="justify" style="font-weight:bold;">Name of CheckPoint</td>
                    <td colspan="2" align="center" style="font-weight:bold;">Visit Time</td>
                    <td colspan="1" align="center" style="font-weight:bold;">Remark</td>
                    <td colspan="1" align="center" style="font-weight:bold;">Status</td>
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
                    <td colspan="1" align="center">{{$srNo}}</td>
                    <td colspan="5" align="justify">{{$check->pointName}}</td>
                    @if(isset($tourLogStatus))
                    <td colspan="2" align="center"> {{date("h:i:s a", strtotime($tourLogStatus->time))}}</td>
                    <td colspan="1">{{$tourLogStatus->remark}}</td>
                    <td colspan="1" align="center"><span class="badge badge-success">Checked</span></td>
                    @else
                    <td colspan="2" align="center">-</td>
                    <td colspan="1" align="center">-</td>
                    <td colspan="1" align="center"><span class="badge badge-warning">Missed</span></td>
                    @endif
                </tr>
                <?php $srNo++; ?>

                @endforeach


                
                @endif


                @endforeach


                @endforeach
            </tbody>
        </table>

    </div>
</div>
@include('includes.report-footer')
<script>
    $(document).ready(function() {
        $('#excel').on('click', function() {
            $.ajax({
                type: 'get',
                url: '{{URL::to("downloadDailyTour") }}',
                responseType: 'blob',
                data: {
                    'type': $('#type').val(),
                    'geofences': $('#geofences').val(),
                    'tourDate': $('#tourDate').val(),
                    'userId': $('#userId').val(),
                }
            }).then((response) => {
                var url = "{{URL::to('downloadDailyTour')}}"

                window.location = url;
            });
        });
    });
</script>