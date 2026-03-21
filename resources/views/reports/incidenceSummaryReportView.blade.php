@include('includes.report-header')
@php
// dump('in summary report', $client)
@endphp
<div class="row stickyTable">
    <div class="col-md-11">
        <table class="table">
            <thead style="min-width: 70px;">
                <tr>
                    <th colspan="3" style="text-align: center;">Organization</th>
                    @if($client != 'all')
                    <th colspan="3" style="text-align: center;">Client</th>
                    @endif
                    <!-- <th colspan="3" style="text-align: center;">Site</th> -->
                    <th colspan="3" style="text-align: center;">Date Range</th>
                    <th colspan="3" style="text-align: center;">Report Type</th>
                    <th colspan="3" style="text-align: center;">Generated On</th>

                </tr>
            </thead>
            <tbody style="min-width: 70px;">
                <tr>
                    <td colspan="3" style="text-align: center;"><?php
                                                                $user = session('user');

                                                                if ($geofences != 'all') {
                                                                    $site = App\SiteDetails::where('id', $geofences)->first();
                                                                } else {
                                                                    $site = App\SiteDetails::where('client_id', $client)->first();
                                                                }

                                                                $companyName = App\CompanyDetails::where('id', $user->company_id)->first();
                                                                ?>
                        {{$companyName->name}}
                    </td>
                    @if($client != 'all')
                    <td colspan="3" style="text-align: center;">
                        <?php

                        if ($geofences != 'all' && $geofences != null) {
                            // dd('if');
                            $clientName = App\ClientDetails::where('id', $site->client_id)->first();
                        } else if ($client != null && $client != 'all') {
                            // dd('else');
                            $clientName = App\ClientDetails::where('id', $client)->first();
                        } else {
                            $clientName = '';
                        }

                        ?>
                        @if($client != null && $client != 'all')
                        @if($clientName != null)
                        {{$clientName->name}}

                        @else
                        NA
                        @endif

                        @endif

                    </td>
                    @endif

                    <!-- <td colspan="3" style="text-align: center;"> </td> -->
                    <td colspan="3" style="text-align: center;"> {{date('d M Y',strtotime($fromDate))}}
                        to {{date('d M Y',strtotime($toDate))}} </td>
                    <td colspan="3" style="text-align: center;">Incidence Summary Report</td>
                    <td colspan="3" style="text-align: center;"><?php echo date("d M Y"); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="col-md-1" style="text-align: center;margin-top: -10px;">
        <div class="row">
            <form method="post" action='{{ route("downloadIncidenceReport") }}' target="_blank">
                @csrf
                <input type="hidden" name="geofences" value={{$geofences}} />
                <input type="hidden" name="client" value={{$client}} />
                <input type="hidden" name="toDate" value={{$toDate}} />
                <input type="hidden" name="fromDate" value={{$fromDate}} />
                <input type="hidden" name="incidenceSubType" value="{{$incidenceSubType}}" />
                <input type="hidden" name="daysCount" value={{$daysCount}} />
                <div class="col-md-12" style="display: flex;justify-content: center;">
                    <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true" style="border: 1px solid grey;padding: 3px 8px;border-radius: 50%;">×</button>
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
    <div class="col-md-12" style="overflow: scroll;height:500px">
        <table class="table">
            <tr>
                <td style="background-color:#d6eba9;border-top:1px solid black;font-weight:bold;" align="center">Sr. No.</td>
                <td style="background-color:#d6eba9;border-top:1px solid black;font-weight:bold;" align="center">Date</td>
                @php
                $user = session('user');
                $checkList = DB::table('incidence_checklist')->where('type_id' ,'=' ,NULL)->where('company_id',$user->company_id)->get();

                $checkArray = $checkList->pluck('id')->toArray();

                @endphp
                @foreach($checkList as $row)
                <td style="background-color:#d6eba9;border-top:1px solid black;font-weight:bold;" align="center">
                    {{$row->name}}
                </td>
                @endforeach
                <td style="background-color:#d6eba9;border-top:1px solid black;font-weight:bold;" align="center">Total</td>
            </tr>

            <?php $srNo = 1; ?>
            <?php
            // $total=0;
            $totalIncidence = 0;
            $daysArray = [];
            $startDate = date('Y-m-d', strtotime($fromDate));
            $fromDate = date('d-m-Y', strtotime($fromDate));
            $daysArray[] = (object)[
                'date' => $fromDate,
            ];
            $datee = $fromDate; ?>
            @for($i = 1; $i < $daysCount ; $i++) <?php
                                                    $datee = date('d-m-Y', strtotime('+1 day', strtotime($datee)));
                                                    $daysArray[] = (object)[
                                                        'date' => $datee,
                                                    ];
                                                    ?> @endfor <?php
                                                                foreach ($daysArray as $index => $item) {
                                                                    foreach ($data as $val) {
                                                                        if ($val->dateFormat == $item->date) {

                                                                            $dateIn = $daysArray[$index]->date;
                                                                            $daysArray[$index] = (object) [
                                                                                'date' => $dateIn,
                                                                            ];
                                                                        }
                                                                    }
                                                                } ?> <?php $percent = 0; ?> @foreach($daysArray as $item) <tr>
                <td align="center" colspan="1">{{$srNo++}}</td>
                <td align="center" colspan="1">{{$item->date}}</td>
                @foreach($checkList as $row)
                <?php
                $user = session('user');
                //dd($user);
                $date = date('Y-m-d', strtotime($item->date));

                // $IncidenceDetails = App\IncidenceDetails::where('dateFormat', $date)->where('checkListId', $row->id)->where('site_id', $geofences)->count();
                $IncidenceDetails = App\IncidenceDetails::where('dateFormat', $date)
                    ->where('checkListId', $row->id)
                    ->when($geofences !== 'all', function ($query) use ($geofences) {
                        return $query->where('site_id', $geofences);
                    })
                    ->when($geofences === 'all' && $client !== 'all', function ($query) use ($client) {
                        return $query->where('client_id', $client);
                    })
                    ->when($geofences === 'all' && $client === 'all', function ($query) use ($user) {
                        return $query->where('company_id', $user->company_id);
                    })
                    ->count();
                // dump($IncidenceDetails);
                // $total = App\IncidenceDetails::where('dateFormat', $date)->whereIn('checkListId', $checkArray)->where('company_id', $user->company_id)->where('site_id', $geofences)->count();
                $total = App\IncidenceDetails::where('dateFormat', $date)
                    ->whereIn('checkListId', $checkArray)
                    ->when($geofences !== 'all', function ($query) use ($geofences) {
                        return $query->where('site_id', $geofences);
                    })
                    ->when($geofences === 'all' && $client !== 'all', function ($query) use ($client) {
                        return $query->where('client_id', $client);
                    })
                    ->when($geofences === 'all' && $client === 'all', function ($query) use ($user) {
                        return $query->where('company_id', $user->company_id);
                    })
                    ->count();
                // $totalIncidence += $total;
                // dd($total , "total");
                ?>
                @if( $IncidenceDetails >0)
                <td align="center" colspan="1"><span onclick="incidenceSummary('{{$item->date}}','{{$row->name}}','{{$geofences}}')" style="color:#003add;">{{$IncidenceDetails}}</span></td>

                @else
                <td align="center" colspan="1">{{$IncidenceDetails}}</td>
                @endif
                @endforeach
                <td align="center" colspan="1" style="background-color:#B8CCE4;border-top:1px solid black;">{{$total}}</td>

                </tr>
                @endforeach
                <tr>
                    <td align="center" style="font-weight: bold;background-color:#B8CCE4" colspan="2">Total</td>
                    @foreach($checkList as $row)
                    <?php $date = date('Y-m-d', strtotime($item->date));
                    // $bottomTotal = App\IncidenceDetails::whereBetween('dateFormat', [$startDate, $toDate])->where('checkListId', $row->id)->where('site_id', $geofences)->count(); 
                    $bottomTotal = App\IncidenceDetails::whereBetween('dateFormat', [$startDate, $toDate])
                        ->where('checkListId', $row->id)
                        ->when($geofences !== 'all', function ($query) use ($geofences) {
                            return $query->where('site_id', $geofences);
                        })
                        ->when($geofences === 'all' && $client !== 'all', function ($query) use ($client) {
                            return $query->where('client_id', $client);
                        })
                        ->when($geofences === 'all' && $client === 'all', function ($query) use ($user) {
                            return $query->where('company_id', $user->company_id);
                        })
                        ->count();

                    ?>
                    <td align="center" colspan="1" style="background-color:#B8CCE4;border-top:1px solid black;">{{$bottomTotal}}</td>
                    <?php $totalIncidence = $totalIncidence + $bottomTotal; ?>

                    @endforeach
                    <td align="center" colspan="1" style="background-color:#d6eba9;border-top:1px solid black;" align="center">{{$totalIncidence}}</td>
                </tr>
        </table>
    </div>
</div>

@include('includes.report-footer')