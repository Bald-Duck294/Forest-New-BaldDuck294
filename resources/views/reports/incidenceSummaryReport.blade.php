<style>
    table {
        border-collapse: collapse;
        width: 200%;
        background: rebeccapurple
    }

    th,
    td {
        /* background: rebeccapurple */

        border: 1px solid black;
        padding: 5px;
        text-align: center;
    }

    .header-cell {
        background-color: #B8CCE4;
        font-weight: bold;
    }

    .data-cell {
        background-color: #ffffff;
    }

    .total-cell {
        background-color: #d6eba9;
        font-weight: bold;
    }

    .subtotal-cell {
        background-color: #B8CCE4;
    }
</style>
<table style="width: 100%; border-collapse: collapse;">
    <tbody>
        <tr>
            <th colspan="2" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Organization</th>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Client</th>
            <!-- <th colspan="2" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Site</th> -->
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Date Range</th>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Report type</th>
            <th colspan="1" style="text-align: center; background-color:#B8CCE4;font-weight:bold;padding:5px;border: 1px solid black;">Generated On</th>

        </tr>
        <tr>
            <?php
            $user = session('user');
            if ($_REQUEST['geofences'] != 'all') {
                $site = App\SiteDetails::where('id', $_REQUEST['geofences'])->first();

                $companyName = App\CompanyDetails::where('id', $user->company_id)->first();
            } else {

                // $site = App\SiteDetails::where('client_id', $_REQUEST['client'])->get();
                $site = App\SiteDetails::where('client_id', $user->company_id)->get();
                //    dd($site);
                $companyName = App\CompanyDetails::where('id', $user->company_id)->first();
            }

            ?>

            <td colspan="2" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">{{$companyName->name}} </td>
            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;"> <?php
                                                                                                                        if ($_REQUEST['geofences'] != 'all' && $_REQUEST['geofences'] != null) {

                                                                                                                            $clientName = App\ClientDetails::where('company_id', $user->company_id)->first();
                                                                                                                            // dump('in  if' ,$user->company_id );
                                                                                                                            // dump('in  if' ,$clientName->name );
                                                                                                                        } else if ($_REQUEST['client'] != null && $_REQUEST['client'] != 'all') {
                                                                                                                            // dump('in else if');
                                                                                                                            $clientName = App\ClientDetails::where('id', $_REQUEST['client'])->first();
                                                                                                                        } else {
                                                                                                                            $clientName = (object) [
                                                                                                                                "name" => "All"
                                                                                                                            ];
                                                                                                                            // dump('in else ');
                                                                                                                        }

                                                                                                                        // dd('cleitn name 43' , $clientName);

                                                                                                                        ?>
                {{-- {{$clientName->name}} --}}
                {{ $clientName ? $clientName->name : '-' }}
            </td>

            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">{{date('d M Y',strtotime($_REQUEST['fromDate']))}}
                to {{date('d M Y',strtotime($_REQUEST['toDate']))}}
            </td>
            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;">Incidence Summary Report</td>
            <td colspan="1" style="text-align: center;background-color:#B8CCE4;padding:5px;border: 1px solid black;"><?php echo date("d M Y"); ?></td>
        </tr>
        @if($_REQUEST['xlsx'] == 'pdf')
        <tr>
            <td style="border: none !important;" colspan="10"></td>
        </tr>
        <tr>
            <td style="border: none !important;" colspan="10"></td>
        </tr>
        <tr>
            <td style="border: none !important;" colspan="10"></td>
        </tr>
        @else
        <tr>
            <td style="border: none !important;" colspan="10"></td>
        </tr>

        @endif


        <tr>
            <th style="background-color:#d6eba9;text-align:center;font-weight:bold;border: 1px solid black;">Sr. No.</th>
            <th style="background-color:#d6eba9;text-align:center;font-weight:bold;border: 1px solid black;">Date</th>
            @php
            $user = session('user');
            $checkList = DB::table('incidence_checklist')->where('type_id' ,'=' ,NULL)->where('company_id',$user->company_id)->get();

            $checkArray = $checkList->pluck('id')->toArray();
            @endphp
            @foreach($checkList as $row)
            <th style="background-color:#d6eba9;text-align:center;font-weight:bold;border: 1px solid black;">{{$row->name}}</th>
            @endforeach
            <th style="background-color:#d6eba9;text-align:center;font-weight:bold;border: 1px solid black;">Total</th>
        </tr>

        <?php $srNo = 1;
        $totalIncidence = 0;
        $daysArray = [];
        $startDate = date('Y-m-d', strtotime($fromDate));
        $fromDate = date('d-m-Y', strtotime($_REQUEST['fromDate']));
        $daysArray[] = (object)[
            'date' => $_REQUEST['fromDate'],
        ];
        $datee = $fromDate; ?>
        @for($i = 1; $i < $_REQUEST['daysCount'] ; $i++) <?php
                                                            $datee = date('d-m-Y', strtotime('+1 day', strtotime($datee)));
                                                            $daysArray[] = (object)[
                                                                'date' => $datee,
                                                            ];
                                                            ?> @endfor <?php
                                                                        foreach ($daysArray as $index => $item) {
                                                                            foreach ($IncidenceDetails as $val) {
                                                                                if ($val->dateFormat == $item->date) {
                                                                                    $dateIn = $daysArray[$index]->date;
                                                                                    $daysArray[$index] = (object) [
                                                                                        'date' => $dateIn,
                                                                                    ];
                                                                                }
                                                                            }
                                                                        }
                                                                        ?> <?php $percent = 0; ?> @foreach($daysArray as $item) <tr>

            <td style="text-align:center;border: 1px solid black;">{{$srNo++}}</td>
            <td style="text-align:center;border: 1px solid black;">{{date('d-m-y', strtotime($item->date))}}</td>

            <?php
            // MOVED THESE TWO LINES UP HERE
            $date = date('Y-m-d', strtotime($item->date));
            $user = session('user');
            ?>

            @foreach($checkList as $row)
            <?php


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

            ?>

            @if($IncidenceDetails > 0)
            <td style="text-align:center;border: 1px solid black;">{{$IncidenceDetails}}</td>
            @else
            <td style="text-align:center;border: 1px solid black;">-</td>
            @endif
            @endforeach
            <?php
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

            ?>
            <td style="background-color:#B8CCE4;text-align:center;border: 1px solid black;">{{$total != 0 ? $total : '-' }}</td>
            </tr>
            @endforeach

            <tr>
                <td colspan="2" style="background-color:#d6eba9;text-align:center;border: 1px solid black;">Total</td>
                @foreach($checkList as $row)
                <?php
                $date = date('Y-m-d', strtotime($item->date));

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
                <td style="background-color:#d6eba9;text-align:center;border: 1px solid black;">{{$bottomTotal != 0 ? $bottomTotal : '-' }}</td>
                <?php $totalIncidence = $totalIncidence + $bottomTotal; ?>
                @endforeach
                <td style="background-color:#d6eba9;text-align:center;border: 1px solid black;">{{$totalIncidence != 0 ? $totalIncidence : '-' }}</td>
            </tr>
    </tbody>
</table>