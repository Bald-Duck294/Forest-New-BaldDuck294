@include('includes.report-header')
@php
// dump('In clientSupervisor report');
// dump($subType , $currentDate , $date, "data")
// dump($clientName , "client name");
@endphp
<div class="row stickyTable">
    <div class="col-md-11">
        <table class="table">
            <thead style="min-width: 70px;">
                <tr style="background-color:#fcd7a9;">
                    <th colspan="3" style="text-align: center;">Organization</th>
                    <th colspan="3" style="text-align: center;">Date Range</th>
                    <th colspan="3" style="text-align: center;">Report Type</th>
                    <th colspan="3" style="text-align: center;">Generated On</th>
                </tr>
            </thead>
            <tbody style="min-width: 70px;">

                <tr>
                    <td colspan="3" style="text-align: center;">{{ $companyName }}</td>
                    <td colspan="3" style="text-align: center;"> {{ $date  }} </td>
                    <td colspan="3" style="text-align: center;"> @if(isset($subType)) {{$subType}} @else N/A @endif</td>
                    <td colspan="3" style="text-align: center;">{{$generatedOn }}</td>
                </tr>

            </tbody>
        </table>
    </div>
    <div class="col-md-1" style="text-align: center;margin-top: -10px;">
        <div class="row">
            <form method="post" action="{{ route('downloadAllSupervisorAttendance') }}" target="_blank">
                @csrf

                <input type="hidden" name="fromdate" value={{$fromDate}} />
                <input type="hidden" name="todate" value={{$toDate}} />
                <input type="hidden" name="guard" value={{$guard}} />
                <input type="hidden" name="attendanceSubType" value={{$attendanceSubType}} />
                <input type="hidden" name="subType" value={{$subType}} />
                <input type="hidden" name="client" value={{$client}} />
                <input type="hidden" name="geofences" value={{$geofences}} />
                <input type="hidden" name="supervisorSelect" value={{$supervisorSelect}} />


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
    <div class="col-md-12" style="overflow: scroll;height:90vh;">
        <!-- <table class="table {{ $type == 'pdf' ? 'pdf-table' : '' }}"> -->
        <table>
            <?php $datee = $startDatee;
            $dailyCountArray = [];

            ?>
            <tbody>
                <tr style="background-color: #d97979;">
                    <?php $datee = $startDatee; ?>

                    <th valign="middle" class="main-heading heading-style">Sr No</th>
                    <th valign="left" class="main-heading heading-style">Employee Name</th>
                    {{-- <th valign="middle" class="main-heading heading-style">Client</th> --}}
                    <th valign="middle" class="main-heading heading-style">Client / Range</th>
                    <th valign="middle" class="main-heading heading-style">Site / Beat</th>
                    <th valign="middle" class="main-heading heading-style">Days Worked</th>

                    <?php
                    $daysArray = [];
                    $daysArray[] = date('Y-m-d', strtotime($datee));
                    // $daysArray[] = $datee;
                    $dateee = date('d-m-y', strtotime($datee));
                    $dateFormat = date('Y-m-d', strtotime($datee));
                    $fdate = date('d', strtotime($datee));
                    $day = date('D', strtotime($datee));
                    // dd($datee , $daysArray  , "days array ");
                    $count = 0;

                    if (array_key_exists($dateFormat, $attendCount) !== false) {

                        $dailyCountArray[] = count($attendCount[$dateFormat]);
                    } else {
                        $dailyCountArray[] = 0;
                    }


                    ?>
                    <th valign="middle" class="main-heading heading-style">{{ $day }}-{{ $fdate }}
                    </th>



                    @for ($i = 1; $i < $daysCount; $i++) <?php
                                                            $dateee = date('Y-m-d', strtotime('+1 day', strtotime($datee)));
                                                            $dateFormat = date('Y-m-d', strtotime('+1 day', strtotime($datee)));
                                                            $fdate = date('d', strtotime('+1 day', strtotime($datee)));
                                                            $day = date('D', strtotime('+1 day', strtotime($datee)));
                                                            $datee = date('Y-m-d', strtotime('+1 day', strtotime($datee)));
                                                            // dump($datee , "date");
                                                            $daysArray[] = $datee;
                                                            $count++;
                                                            ?>
                        @php if (array_key_exists($dateFormat, $attendCount) !==false)
                        {
                        $dailyCountArray[]=count($attendCount[$dateFormat]);

                        } else {
                        $dailyCountArray[]=0;
                        }
                        @endphp
                        <th valign="middle" class="main-heading heading-style">
                        {{ $day }}-{{ $fdate }}</th>
                        @endfor
                </tr>

                <?php $srNo = 0;
                $daysCount = $daysCount;
                ?>
                @foreach ($data as $key => $item)

                <?php
                if (isset($weekoffs[$key])) {
                    $days = $weekoffs[$key][0];
                } else {
                    $days = [];
                }

                // dump($item);
                $param = array_unique($item); // Remove duplicates & reindex
                // Check if all values are null, empty, or missing
                $filteredParam = array_filter($param, function ($value) {
                    return !is_null($value) && $value !== '';
                });

                $acount = count($filteredParam);

                $srNo++;

                ?>

                <tr>
                    <td style="border:1px solid black;text-align:center;">{{ $srNo }}</td>
                    <td style="border:1px solid black;">{{ $names[$key][0] }}</td>
                    {{-- @if(isset($clients[$key]))

                    <td style="border:1px solid black;text-align:center;">{{ $clients[$key][0] }}</td>
                    @else

                    <td style="border:1px solid black;text-align:center;">NA</td>
                    @endif --}}
                    @if(isset($supervisorSites[$key]))
                    <td style="border:1px solid black;text-align:center;">
                        @foreach($supervisorSites[$key]['client'] as $clientkey => $val)
                        {{$val}} @if($clientkey != array_key_last($supervisorSites[$key]['client'])) , @endif &nbsp;
                        @endforeach
                    </td>

                    <td style="border:1px solid black;text-align:center;">
                        @foreach($supervisorSites[$key]['site'] as $sitekey => $val)
                        {{$val}} @if($sitekey != array_key_last($supervisorSites[$key]['site'])) , @endif &nbsp;
                        @endforeach
                    </td>
                    @else
                    <td style="border:1px solid black;text-align:center;">NA</td>
                    <td style="border:1px solid black;text-align:center;">NA</td>
                    @endif

                    <td style="border:1px solid black;text-align:center;">{{ $acount }}</td>
                    @php
                    // dump($daysArray , "arr");
                    @endphp
                    @for($i = 0; $i < $daysCount; $i++)
                        <?php
                        // dump($daysArray , "arr");
                        $index = array_search($daysArray[$i], $param);
                        // dump($index , "index");
                        // dump($index ,$daysArray[$i],$daysArray,$param,"index");
                        // dump($i, $index , $param);
                        // dump($i , $index)
                        ?>

                        @if($index !==false)
                        {{-- // Before the condition, add these checks --}}
                        @php
                        // var_dump($days);
                        // var_dump(json_decode($days));
                        // var_dump(date('l', strtotime($daysArray[$i])));
                        @endphp
                        <td class='border-present-class' style="border:1px solid black;text-align:center;">P</td>
                        @elseif($days && !empty(json_decode($days)) &&
                        array_search(date('l', strtotime($daysArray[$i])), json_decode($days)) !== false)
                        <td class='border-wo-class' style="border:1px solid black;text-align:center;">WO</td>
                        @else
                        <td class='border-absent-class' style="border:1px solid black;text-align:center;">A</td>
                        @endif

                        @endfor
                </tr>

                @endforeach

                <tr>
                    <td style="border:1px solid black;text-align:center;" class="bottom-border-class" colspan=5>
                        Daily Attendance Count </td>

                    @for($i = 0; $i < $daysCount; $i++)
                        <td style="border:1px solid black;text-align:center;" class="bottom-border-class">

                        {{ $dailyCountArray[$i] == 0 ? "0" : $dailyCountArray[$i] }}</td>
                        </td>
                        @endfor

                </tr>

            </tbody>
        </table>
        <!-- </table> -->
    </div>
</div>
@include('includes.report-footer')

<style type="text/css">
    .report-info {
        font-weight: bold;
        font-size: 13;
    }

    .border-class {
        border: 1px solid #000;
        background-color: #E2E2E2;
        text-align: center;
    }

    .bottom-border-class {
        border: 1px solid #000;
        /* background-color: #b8cce4; */
        background-color: #d97979;
        text-align: center;
    }

    .heading-class2 {
        border: 1px solid #000;
        background-color: #E2E2E2;
        text-align: center;
        margin: auto;
        height: 20
    }

    .border-present-class {
        border: 1px solid #000;
        color: #00873d;
        height: 15;
        text-align: center;
    }

    .border-absent-class {
        border: 1px solid #000;
        color: #a11d1d;
        height: 15;
        text-align: center;
    }

    .border-wo-class {
        border: 1px solid #000;
        background-color: #fafafa;
        height: 15;
        text-align: center;
    }

    .border-class-plain {
        border: 1px solid #000;
        /* background-color: #E2E2E2; */
        height: 15
    }

    .heading-style {
        text-align: center;
        font-size: 14;
        font-weight: bold;
    }

    .main-heading {
        border: 1px solid #000;
        /* font-size: 14px !important; */
        /* background-color: #b8cce4; */
        background-color: #d97979;

    }
</style>