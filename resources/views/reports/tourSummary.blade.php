<table style="border-collapse:collapse;width:100%;justify-content:center;">
    <tbody>
        <tr>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Organization</th>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Client</th>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Site</th>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Date Range</th>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Report Type</th>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Generated On</th>

        </tr>
        <tr>
            @php
            $site = App\SiteDetails::where('id', $geo)->first();
            $companyName = App\CompanyDetails::where('id', $site->company_id)->first();
            @endphp

            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;"> {{$companyName->name}}</td>
            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">{{$site->client_name}}</td>
            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">{{$site->name}}</td>
            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;"> {{date('d M Y',strtotime($startDate))}}
                to {{date('d M Y',strtotime($endDate))}}</td>
            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">Tour Summary Report</td>
            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;"><?php echo date("d M Y"); ?></td>
        </tr>
        @if($_REQUEST['xlsx'] == 'pdf')
        <tr>
            <td colspan="10"></td>
        </tr>
        <tr>
            <td colspan="10"></td>
        </tr>
        <tr>
            <td colspan="10"></td>
        </tr>
        @else
        <tr>
            <td colspan="10"></td>
        </tr>
        @endif


        <tr>
            <td colspan="1" style="font-weight:bold;background-color:#d6eba9;border:1px solid black;;border: 1px solid black;" align="center">Sr. No.</td>
            <td colspan="1" style="font-weight:bold;background-color:#d6eba9;border:1px solid black;;border: 1px solid black;" align="center">Date</td>
            @php
            $guardTour = App\GuardTour::where('site_id',$geo)->get();
            @endphp
            @foreach($guardTour as $row)
            <td colspan="1" style="background-color:#d6eba9;border:1px solid black;;border: 1px solid black;" align="center">{{$row->tour_name}}</td>
            @endforeach
            <th colspan="1" style="background-color:#d6eba9;border:1px solid black;;border: 1px solid black;font-weight:bold;" align="center">Total Rounds</th>
            <th colspan="1" style="background-color:#d6eba9;border:1px solid black;;border: 1px solid black;font-weight:bold;" align="center">Percentage</th>
        </tr>

        <?php $srNo = 1; ?>
        <?php
        $totalPercentage = 0;
        $daysArray = [];
        $fromDate = date('d-m-Y', strtotime($startDate));
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
                                                                foreach ($GuardTourLog as $val) {
                                                                    if ($val->tourDate == $item->date) {

                                                                        $dateIn = $daysArray[$index]->date;
                                                                        $daysArray[$index] = (object) [
                                                                            'date' => $dateIn,
                                                                        ];
                                                                    }
                                                                }
                                                            } ?> <?php $percent = 0; ?> @foreach($daysArray as $item) <tr>
            <td align="center" colspan="1" style="border: 1px solid black;">{{$srNo++}}</td>
            <td align="center" colspan="1" style="border: 1px solid black;">{{$item->date}}</td>
            <?php
            $date = date('Y-m-d', strtotime($item->date));
            $totalRounds = 0;
            $completedRounds = 0;
            $percentcompletedRounds = 0;
            $percenttotalRounds = 0;
            $totalFinalPercent = 0;

            foreach ($guardTour as $tour) {
                // dd($guardTour);
                $totalRound = 0;

                $round = App\GuardTour::where('id', $tour->id)->first();
                if ($round != null && $round->timeArray != null) {
                    $timeArray = json_decode($round->timeArray);
                    $totalRound = count($timeArray);
                    $totalRounds = $totalRounds + $totalRound;
                }
                $roundCompleted = App\GuardTourLog::where('tourId', $tour->id)->where('tourDate', $date)->where('isEnded', '0')->whereNotNull('endTime')->count();
                $completedRounds = $completedRounds + $roundCompleted;
            ?>
                <td align="center" style="border: 1px solid black;">{{$roundCompleted}} / {{$totalRound}}</td>

            <?php
            } ?>
            @if($totalRounds > 0)
            <td align="center" style="border: 1px solid black;">{{$completedRounds}} / {{$totalRounds}}</td>
            @else
            <td align="center" style="border: 1px solid black;">0</td>
            @endif

            @if($totalRounds > 0)
            <?php $percentage = ($completedRounds / $totalRounds) * 100; ?>
            <td align="center" style="border: 1px solid black;">{{round($percentage,2)}} %</td>
            @else
            <?php $percentage = 0; ?>
            <td align="center" style="border: 1px solid black;">0</td>
            @endif


            </tr>

            <?php $percent = $percent + $percentage; ?>
            @endforeach

            <tr>
                <td align="center" style="font-weight: bold;background-color:#B8CCE4;border: 1px solid black;" colspan="2">Percentage</td>

                @foreach($guardTour as $row)
                <?php $tourRounds = App\GuardTour::where('id', $row->id)->first();

                $roundCompleteds = App\GuardTourLog::where('tourId', $row->id)->whereBetween('tourDate', [$startDate, $endDate])->whereNotNull('endTime')->where('isEnded', 0)->count();
                if ($tourRounds != null && $tourRounds->timeArray != null) {
                    $totalTimeArray = count(json_decode($tourRounds->timeArray));
                    $totalDays = ($totalTimeArray * count($daysArray));
                    $percenttotalRounds = $percenttotalRounds + $totalTimeArray;
                }
                $percentcompletedRounds = $percentcompletedRounds + $roundCompleteds;
                $tottalPercent = ($roundCompleteds / $totalDays) * 100;

                $totalFinalPercent = $totalFinalPercent + $tottalPercent;

                ?>
                <td align="center" style="font-weight: bold;background-color:#B8CCE4;border: 1px solid black;">{{round($tottalPercent,2)}} %</td>
                @endforeach
                <?php
                $totalRoundPercent = ($percenttotalRounds * count($daysArray));
                $totalCompletedRounds = $percentcompletedRounds;
                $finalPercent = $percent / count($daysArray);

                ?>
                <td align="center" style="font-weight: bold;background-color:#B8CCE4;border: 1px solid black;">{{round($finalPercent,2)}} %</td>


                <td align="center" style="font-weight: bold;background-color:#B8CCE4;border: 1px solid black;">{{round($finalPercent,2)}} %</td>
            </tr>
    </tbody>
</table>