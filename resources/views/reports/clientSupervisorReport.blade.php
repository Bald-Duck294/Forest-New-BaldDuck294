@php
//dump($subType , "start date");
// dd($supervisorSites , 'sites');
@endphp
<table style="border-collapse:collapse;width:100%;justify-content:center;">
    <?php $datee = $startDatee;
    $dailyCountArray = []; ?>
    <tbody>
        <tr>
            <th colspan="3"
                style="text-align: center;background-color:#fcd7a9;font-weight:bold;padding:5px;border: 1px solid black;">
                Organization</th>

            <th colspan="3"
                style="text-align: center;background-color:#fcd7a9;font-weight:bold;padding:5px;border: 1px solid black;">
                {{ $dateFormat }}
            </th>
            <th colspan="3"
                style="text-align: center;background-color:#fcd7a9;font-weight:bold;padding:5px;border: 1px solid black;">
                Report type</th>
            <th colspan="3"
                style="text-align: center;background-color:#fcd7a9;font-weight:bold;padding:5px;border: 1px solid black;">
                Generated On</th>

        </tr>
        <tr>

            <td colspan="3" style="text-align: center;background-color:#fcd7a9;padding:5px;border: 1px solid black;">
                {{ $companyName }}
            </td>

            <td colspan="3" style="text-align: center;background-color:#fcd7a9;padding:5px;border: 1px solid black;">
                {{ $date }}
            </td>
            <td colspan="3" style="text-align: center;background-color:#fcd7a9;padding:5px;border: 1px solid black;">
                @if(isset($subType))
                {{$subType}}
                @else N/A @endif
            </td>
            <td colspan="3" style="text-align: center;background-color:#fcd7a9;padding:5px;border: 1px solid black;">
  {{ $generatedOn }}
            </td>
        </tr>
        @if ($flag == 'pdf')
        <tr>
            <td colspan="3"></td>
            <td colspan="3"></td>
            <td colspan="3"></td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td colspan="3"></td>
            <td colspan="3"></td>
            <td colspan="3"></td>
        </tr>
        <tr>
            <td colspan="3"></td>
            <td colspan="3"></td>
            <td colspan="3"></td>
            <td colspan="3"></td>
        </tr>
        @else
        <tr>
            <td colspan="3"></td>
            <td colspan="3"></td>
            <td colspan="3"></td>
            <td colspan="3"></td>
        </tr>
        @endif
        <tr>
            <?php $datee = $startDatee; ?>

            <th style="background-color:#d97979;text-align:center;font-weight:bold;border: 1px solid black;">Sr No</th>
            <th style="background-color:#d97979;text-align:center;font-weight:bold;border: 1px solid black;">Employee
                Name</th>
            <th style="background-color:#d97979;text-align:center;font-weight:bold;border: 1px solid black;">Client /
                Range </th>
            <th style="background-color:#d97979;text-align:center;font-weight:bold;border: 1px solid black;">Site / Beat
            </th>
            <th style="background-color:#d97979;text-align:center;font-weight:bold;border: 1px solid black;">Days Worked
            </th>

            <?php
            $daysArray = [];
            $daysArray[] = date('Y-m-d', strtotime($datee));
            // $daysArray[] = $datee;
            $dateee = date('Y-m-d', strtotime($datee));
            $dateFormat = date('Y-m-d', strtotime($datee));
            $fdate = date('d', strtotime($datee));
            $day = date('D', strtotime($datee));

            $count = 0;
            ?>
            @php
            if (array_key_exists($dateFormat, $attendCount) !== false) {
            $dailyCountArray[] = count($attendCount[$dateFormat]);
            } else {
            $dailyCountArray[] = 0;
            }
            @endphp
            <th style="background-color:#d97979;text-align:center;font-weight:bold;border: 1px solid black;">
                {{ $day }}-{{ $fdate }}
            </th>
            @for ($i = 1; $i < $daysCount; $i++)
                <?php
                $dateee = date('Y-m-d', strtotime('+1 day', strtotime($datee)));
                $dateFormat = date('Y-m-d', strtotime('+1 day', strtotime($datee)));
                $fdate = date('d', strtotime('+1 day', strtotime($datee)));
                $day = date('D', strtotime('+1 day', strtotime($datee)));
                $datee = date('Y-m-d', strtotime('+1 day', strtotime($datee)));
                // dump($datee , "date");
                $daysArray[] = $datee;
                $count++;
                ?> @php
                if (array_key_exists($dateFormat, $attendCount) !==false) {
                $dailyCountArray[]=count($attendCount[$dateFormat]);
                } else {
                $dailyCountArray[]=0;
                }
                @endphp
                <th style="background-color:#d97979;text-align:center;font-weight:bold;border: 1px solid black;">
                {{ $day }}-{{ $fdate }}
                </th>
                @endfor

        </tr>



        <?php
        $srNo = 0;
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
            <td style="border:1px solid black;text-align:center;">{{ $names[$key][0] }}</td>

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

            @if($acount)
            <td style="text-align:center;border: 1px solid black;">{{ $acount ?? '-' }}</td>
            @else
            <td style="text-align:center;border: 1px solid black;"> {{ '-' }} </td>
            @endif

            @for($i = 0; $i < $daysCount; $i++)
                <?php
                // dump($daysArray , "arr");
                $index = array_search($daysArray[$i], $param);
                // dd($daysArray, $daysArray[$i]);
                // dump($i, $index , $param);
                ?>

                @if($index !==false)
                <td style="text-align:center;border: 1px solid black;color:#00873d;">P</td>
                @elseif($days && array_search(date('l', strtotime($daysArray[$i])), json_decode($days)) !== false)
                <td style="text-align:center;border: 1px solid black;color:#D9B611;">WO</td>
                @else
                <td style="text-align:center;border: 1px solid black;color:red;">A</td>
                @endif

                @endfor
        </tr>
        @endforeach
        <tr>
            <td style="font-weight: bold;background-color:#d97979;border: 1px solid black;text-align: center;"
                colspan=5>Daily Attendance Count</td>
            @for($i = 0; $i < $daysCount; $i++)
                <td style="background-color:#d97979;border: 1px solid black;text-align: center;">
                {{ $dailyCountArray[$i] }}
                </td>
                @endfor
        </tr>
    </tbody>
</table>