@php
    // Expecting $patrols passed from controller (collection of PatrolSession)
    //dump($patrols)
    //dump($clientId , $org , $beatId , $employeeId , $reportTitle , "report title ")
@endphp

@include('includes.report-header')

<div class="container-fluid">
    <div class="row">
        <div class="col-md-11">
            <table class="table" style="background-color: #fcd7a9">
                <thead>
                    <tr>
                        <th colspan="3" style="text-align: center;">Organization</th>
                        <th colspan="3" style="text-align: center;">Report Type</th>
                        <th colspan="3" style="text-align: center;">Generated On</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="3" style="text-align: center;">{{ $org ?? 'N/A' }}</td>
                        <td colspan="3" style="text-align: center;">Patrol Sessions Report</td>
                        <td colspan="3" style="text-align: center;">{{ date('d M Y') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="col-md-1" style="text-align: center;margin-top: -10px;">
            <div class="row">
                <form method="post" action="{{ route('downloadPatrollingStatusReport') }}" target="_blank">
                    @csrf
                    <input type="hidden" name="patrols" value="{{ json_encode($patrols) }}" />
                    <input type="hidden" name="client" value="{{ $clientId }}" />
                    <input type="hidden" name="geofences" value="{{ $beatId }}" />
                    <input type="hidden" name="subType" value="{{ $reportTitle }}" />
                    <input type="hidden" name="guard" value="{{ $employeeId }}" />
                    <input type="hidden" name="fromDate" value="{{ $startDate }}" />
                    <input type="hidden" name="toDate" value="{{ $endDate }}" />




                    <div class="col-md-12" style="display: flex;justify-content: center;">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true"
                            style="border: 1px solid grey;padding: 3px 8px;border-radius: 50%;">×</button>
                    </div>
                    <div class="col-md-12" style="padding: 3px;display: flex;justify-content: center;">
                        <button type="submit" class="btn btn-danger btn-border btn-round" name="format" value="pdf">
                            <i class="la la-download" title="pdf"></i>PDF
                        </button>
                    </div>
                    <div class="col-md-12" style="padding: 3px;display: flex;justify-content: center;">
                        <button type="submit" class="btn btn-success btn-border btn-round" name="format" value="xlsx">
                            <i class="la la-download" title="excel"></i>Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="row">
        <div class="col-md-12" style="overflow: auto; max-height: 70vh;">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr style="background-color: #d97979;">
                        <th style="text-align: center; white-space: nowrap;">Sr. No.</th>
                        <th style="text-align: center; white-space: nowrap;">User Name</th>
                        @if($clientId == 'all')
                            <th style="text-align: center; white-space: nowrap;">Range</th>
                        @endif

                        <th style="text-align: center; white-space: nowrap;">Beat Name</th>
                        <th style="text-align: center; white-space: nowrap;">Type</th>
                        <th style="text-align: center; white-space: nowrap;">Session</th>
                        <th style="text-align: center; white-space: nowrap;">Start Time</th>
                        <th style="text-align: center; white-space: nowrap;">End Time</th>
                        <th style="text-align: center; white-space: nowrap;">Start Location</th>
                        <th style="text-align: center; white-space: nowrap;">End Location</th>
                        <th style="text-align: center; white-space: nowrap;">Distance</th>
                        <th style="text-align: center; white-space: nowrap;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($patrols as $index => $item)
                        <tr>
                            <td align="center">{{ $index + 1 }}</td>
                            <td align="center">{{ $item->user->name ?? 'no user' }}</td>

                            @if($clientId == 'all')
                                <td style="border: 1px solid black;">
                                    {{ ($item->site !== null) ? $item->site->client_name : 'N/A' }}
                                </td>
                            @endif
                            <td align="center">{{ $item->display_site ?? 'no site'}}</td>
                            <td align="center">{{ $item->type }}</td>
                            <td align="center">{{ $item->session }}</td>

                            <td align="center">
                                {{ date('d-m-Y h:i a', strtotime($item->started_at)) }}
                            </td>

                            <td align="center">
                                @if ($item->ended_at)
                                    {{ date('d-m-Y h:i a', strtotime($item->ended_at)) }}
                                @else
                                    NA
                                @endif
                            </td>

                            <td align="center">
                                <a href="https://maps.google.com/?q={{ $item->start_lat }},{{ $item->start_lng }}"
                                    target="_blank">
                                    {{ $item->start_lat }}, {{ $item->start_lng }}
                                </a>
                            </td>

                            <td align="center">
                                @if ($item->end_lat && $item->end_lng)
                                    <a href="https://maps.google.com/?q={{ $item->end_lat }},{{ $item->end_lng }}"
                                        target="_blank">
                                        {{ $item->end_lat }}, {{ $item->end_lng }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>


                            <td align="center" style="font-weight: bold;">
                                {{ $item->distance !== null ? round($item->distance / 1000, 2) . ' km' : '-' }}
                            </td>

                            <td align=" center"
                                style="font-weight: bold;
                                                                                        color: {{ $item->ended_at ? 'green' : 'orangered' }};">
                                {{ $item->ended_at ? 'Completed' : 'Ongoing' }}
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>