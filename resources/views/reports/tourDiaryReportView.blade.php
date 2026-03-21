@php
$user = session('user');
$allDataDecoded = json_decode($allData, true);
//dd($allDataDecoded , "all data");
//dump('in tour diary report');
if($allDataDecoded['client']) {
$client = App\ClientDetails::where('id', $allDataDecoded['client'])->first();
}
$site = App\SiteAssign::where('site_id', $allDataDecoded['geofences'])->first();

//dump($subType , "subType");
$routeName = '';
switch($flagType){
case 'self':
$routeName = 'downloadSelfTourDiaryReport';
break;

case 'tour':
$routeName = 'downloadTourDiaryReport';
break;

case 'supervisor' :
$routeName = 'downloadSuperVisorTourDiaryReport';
break;

case 'admin' :
$routeName = 'downloadAdminTourDiaryReport';
break;

}

//dump($flagType , "flag");
@endphp

@include('includes.report-header')

<div class="container-fluid">
    <div class="row">
        <div class="col-md-11">
            <table class="table" style="background-color: #fcd7a9">
                <thead style="min-width: 70px;">
                    <tr>
                        <th colspan="3" style="text-align: center;">Organization</th>
                        @if ($allDataDecoded['client'] !== 'all' && $allDataDecoded['client'] !== null)
                        <th colspan="3" style="text-align: center;">Client Name </th>
                        @endif
                        @if ($allDataDecoded['geofences'] !== 'all' && $allDataDecoded['geofences'] !== null)
                        <th colspan="3" style="text-align: center;">Site Name </th>
                        @endif
                        <th colspan="3" style="text-align: center;">Date Range</th>
                        <th colspan="3" style="text-align: center;">Report Type</th>
                        <th colspan="3" style="text-align: center;">Generated On</th>
                    </tr>
                </thead>

                <tbody style="min-width: 70px;">
                    <tr>
                        <td colspan="3" style="text-align: center;"> {{ $companyName }}</td>
                        @if ($allDataDecoded['client'] !== 'all' && $allDataDecoded['client'] !== null )
                        <td colspan="3">{{ $client->name }}</td>
                        @endif

                        @if ($allDataDecoded['geofences'] !== 'all' && $allDataDecoded['geofences'] !== null)
                        <td colspan="3"> {{ $site->site_name }} </td>
                        @endif

                        <td colspan="3" style="text-align: center;"> {{ $reportMonth }}</td>
                        <td colspan="3" style="text-align: center;"> {{ str_replace('_' , ' ' , $subType) }}</td>
                        <td colspan="3" style="text-align: center;"> {{ date('d M Y') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="col-md-1" style="text-align: center;margin-top: -10px;">
            <div class="row">
                <form method="post" action="{{ route($routeName) }}" target="_blank">
                    @csrf
                    <input type="hidden" name="toDate" value="{{ $toDate }}" />
                    <input type="hidden" name="fromDate" value="{{ $fromDate }}" />
                    <input type="hidden" name="allData" value="{{ $allData }}" />
                    <input type="hidden" name="flagType" value="{{ $flagType }}" />
                    <input type="hidden" name="subType" value="{{ $subType }}" />
                    <input type="hidden" name="supervisorSelect" value="{{ $supervisorSelect ?? '-' }}" />
                    <input type="hidden" name="adminSelect" value="{{ $adminSelect ?? '-' }}" />


                    @if ($allDataDecoded['client'] !== 'all' && $allDataDecoded['client'] !== null )
                    <input type="hidden" name="clientName" value="{{ $client->name }}" />
                    @endif

                    @if ($allDataDecoded['geofences'] !== 'all' && $allDataDecoded['geofences'] !== null)
                    <input type="hidden" name="siteName" value="{{ $site->site_name }}" />
                    @endif

                    <div class="col-md-12" style="display: flex;justify-content: center;">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true"
                            style="border: 1px solid grey;padding: 3px 8px;border-radius: 50%;">×</button>
                    </div>
                    <div class="col-md-12" style="padding: 3px;display: flex;justify-content: center;">
                        <button type="submit" class="btn btn-danger btn-border btn-round" name="xlsx" value="pdf">
                            <i class="la la-download" title="pdf"></i>PDF
                        </button>
                    </div>
                    <div class="col-md-12" style="padding: 3px;display: flex;justify-content: center;">
                        <button type="submit" class="btn btn-success btn-border btn-round" name="xlsx" value="xlsx">
                            <i class="la la-download" title="excel"></i>Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12" style="overflow: auto; max-height: 70vh;">
            <table class="table">
                <thead>
                    <tr style="background-color: #d97979;">
                        <th style="text-align: center; white-space: nowrap;">Sr. No.</th>
                        <th style="text-align: center; white-space: nowrap;">Visited By</th>
                        @if ($allDataDecoded['client'] == 'all')
                        <th style="text-align: center; white-space: nowrap;">Client Name</th>
                        @endif
                        @if($allDataDecoded['client'] == 'all' || $allDataDecoded['geofences'] == 'all')
                        <th style="text-align: center; white-space: nowrap;">Site Name</th>
                        @endif

                        <th style="text-align: center; max-width: 150px; overflow: hidden; text-overflow: ellipsis;">From</th>
                        <th style="text-align: center; white-space: nowrap;">Start Time</th>
                        <th style="text-align: center; white-space: nowrap;">Vehicle</th>
                        <th style="text-align: center; max-width: 150px; overflow: hidden; text-overflow: ellipsis;">To</th>
                        <th style="text-align: center; white-space: nowrap;">End Time</th>
                        <th style="text-align: center; white-space: nowrap;">Work Summary</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $index => $item)
                    <tr>
                        <td align="center">{{ $index + 1 }}</td>
                        <td align="center">{{ $item->user_name }}</td>

                        @if ($allDataDecoded['client'] == 'all')
                        <td align="center">{{ $item->client_name ? $item->client_name : 'N/A' }}</td>
                        @endif

                        @if( $allDataDecoded['client'] == 'all' || $allDataDecoded['geofences'] == 'all')
                        <td align="center">{{ $item->site_name ? $item->site_name : 'N/A' }}</td>
                        @endif


                        <?php
                        $from_location = json_decode($item->from_location);
                        $to_location = json_decode($item->to_location);
                        ?>

                        <td align="center" style="max-width: 150px; overflow: hidden; text-overflow: ellipsis;">
                            @if ($from_location)
                            <a href="{{ 'https://maps.google.com/?q=' . $from_location->lat . ',' . $from_location->lng }}"
                                target="_blank" style="white-space: nowrap;">
                                {{ $item->from_place }}
                            </a>
                            @else
                            {{ $item->from_place }}
                            @endif
                        </td>

                        <td align="center">{{ date('d-m-Y h:i a', strtotime($item->start_time)) }}</td>
                        <td align="center">{{ $item->vehicle }}</td>


                        <td align="center" style="max-width: 150px; overflow: hidden; text-overflow: ellipsis;">
                            @if ($to_location)
                            <a href="{{ 'https://maps.google.com/?q=' . $to_location->lat . ',' . $to_location->lng }}"
                                target="_blank" style="white-space: nowrap;">
                                {{ $item->to_place }}
                            </a>
                            @else
                            {{ $item->to_place ?? '-' }}
                            @endif
                        </td>

                        <td style="border: 1px solid black;white-space:nowrap;">
                            @if ($item->end_time)
                            {{ date('d-m-Y h:i a', strtotime($item->end_time)) }}
                            @else
                            NA
                            @endif
                        </td>
                        <td align="center">{{ $item->remark ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>