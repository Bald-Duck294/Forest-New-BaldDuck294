@php
// dump($allData, "all data");
//dd($allDataDecoded ,"all data");
@endphp
<table style="border-collapse:collapse;width:100%;justify-content:center;">
    <tbody>
        <tr style="background-color: #fcd7a9">
            <th colspan="2" style="text-align: center; font-weight:bold;padding:5px;border: 1px solid black;">
                Organization</th>
            @if ($allData['client'] !== 'all' && $allData['client'] !== null)
            <th colspan="2" style="text-align: center; font-weight:bold;padding:5px;border: 1px solid black;">Client Name </th>
            @endif
            @if ($allData['geofences'] !== 'all' && $allData['geofences'] !== null)
            <th colspan="2" style="text-align: center; font-weight:bold;padding:5px;border: 1px solid black;">Site Name </th>
            @endif
            <th colspan="2" style="text-align: center; font-weight:bold;padding:5px;border: 1px solid black;">
                Date Range</th>
            <th colspan="2" style="text-align: center; font-weight:bold;padding:5px;border: 1px solid black;">
                Report type</th>
            <th colspan="2" style="text-align: center; font-weight:bold;padding:5px;border: 1px solid black;">
                Generated On</th>
        </tr>
        <tr style="background-color: #fcd7a9">
            <td colspan="2" style="text-align: center; padding:5px;border: 1px solid black;">
                {{ $companyName }}
            </td>
            @if ($allData['client'] !== 'all' && $allData['client'] !== null)
            <td colspan="2" style="text-align: center; padding:5px;border: 1px solid black;">{{ $clientName }}</td>
            @endif

            @if ($allData['geofences'] !== 'all' && $allData['geofences'] !== null)
            <td colspan="2" style="text-align: center; padding:5px;border: 1px solid black;"> {{ $siteName }} </td>
            @endif
            <td colspan="2" style="text-align: center; padding:5px;border: 1px solid black;">
                {{ $dateRange }}
            </td>
            <td colspan="2" style="text-align: center; padding:5px;border: 1px solid black;">
               {{ str_replace('_' , ' ' , $subType) }}  </td>
            <td colspan="2" style="text-align: center; padding:5px;border: 1px solid black;">
                {{ date('d M Y') }}
            </td>
        </tr>

        @if ($_REQUEST['xlsx'] == 'pdf')
        @for ($i = 0; $i < 7; $i++)
            <tr style="margin-bottom:14rem;">
            <td colspan="2"></td>
            <td colspan="2"></td>
            <td colspan="2"></td>
            <td colspan="2"></td>
            </tr>
            @endfor
            @else
            <tr>
                <td colspan="13"></td>
            </tr>
            @endif

            <tr style="margin-bottom: 3rem"></tr>

            <tr style="background-color: #d97979;">
                <th style="border: 1px solid black;font-weight:bold;text-align:center;white-space:nowrap;">Sr. No</th>
                <th style="border: 1px solid black;font-weight:bold;text-align:center;white-space:nowrap;">Visited By</th>

                @if ($allData['client'] == 'all' && $allData['client'] !== null)
                <th style="border: 1px solid black;font-weight:bold;text-align:center;white-space:nowrap;">Client Name</th>
                @endif
                @if($allData['client'] == 'all' || $allData['geofences'] == 'all')
                <th style="border: 1px solid black;font-weight:bold;text-align:center;white-space:nowrap;">Site Name</th>
                @endif
                <th style="border: 1px solid black;font-weight:bold;text-align:center;white-space:nowrap;">From</th>
                <th style="border: 1px solid black;font-weight:bold;text-align:center;white-space:nowrap;">Start Time</th>
                <th style="border: 1px solid black;font-weight:bold;text-align:center;white-space:nowrap;">Vehicle</th>
                <th style="border: 1px solid black;font-weight:bold;text-align:center;white-space:nowrap;">To</th>
                <th style="border: 1px solid black;font-weight:bold;text-align:center;white-space:nowrap;">End Time</th>
                <th style="border: 1px solid black;font-weight:bold;text-align:center;white-space:nowrap;">Work Summary</th>
            </tr>

            <?php $srNo = 1; ?>
            @foreach ($data as $item)
            <tr>
                <td style="background-color: #EAEAEA;border: 1px solid black;text-align:center;white-space:nowrap;">
                    {{ $srNo++ }}
                </td>
                <td style="background-color: #EAEAEA;border: 1px solid black;white-space:nowrap;">
                    {{ $item->user_name }}
                </td>

                @if ($allData['client'] == 'all' && $allData['client'] !== null )
                <td style="background-color: #EAEAEA;border: 1px solid black;white-space:nowrap;">
                    {{ $item->client_name ? $item->client_name : 'N/A' }}
                </td>
                @endif
                @if($allData['client'] == 'all' || $allData['geofences'] == 'all')
                <td style="background-color: #EAEAEA;border: 1px solid black;text-align:center;white-space:nowrap;">
                    {{ $item->site_name ? $item->client_name : 'N/A'}}
                </td>
                @endif


                <td style="background-color: #EAEAEA;border: 1px solid black;max-width:200px;overflow:hidden;text-overflow:ellipsis;">
                    <?php $from_location = json_decode($item->from_location); ?>
                    @if ($from_location)
                    <a href="{{ 'https://maps.google.com/?q=' . $from_location->lat . ',' . $from_location->lng }}"
                        target="_blank" style="white-space:nowrap;">
                        {{ $item->from_place }}
                    </a>
                    @else
                    {{ $item->from_place }}
                    @endif
                </td>
                <td style="background-color: #EAEAEA;border: 1px solid black;white-space:nowrap;">
                    {{ date('d-m-Y h:i a', strtotime($item->start_time)) }}
                </td>
                <td style="background-color: #EAEAEA;border: 1px solid black;white-space:nowrap;">
                    {{ $item->vehicle }}
                </td>
                <td style="background-color: #EAEAEA;border: 1px solid black;max-width:200px;overflow:hidden;text-overflow:ellipsis;">
                    <?php $to_location = json_decode($item->to_location); ?>
                    @if ($to_location)
                    <a href="{{ 'https://maps.google.com/?q=' . $to_location->lat . ',' . $to_location->lng }}"
                        target="_blank" style="white-space:nowrap;">
                        {{ $item->to_place }}
                    </a>
                    @else
                    {{ $item->to_place ?? '-' }}
                    @endif
                </td>
                <td style="background-color: #EAEAEA;border: 1px solid black;white-space:nowrap;">
                    @if ($item->end_time)
                    {{ date('d-m-Y h:i a', strtotime($item->end_time)) }}
                    @else
                    NA
                    @endif
                </td>
                <td style="background-color: #EAEAEA;border: 1px solid black;">
                    {{ $item->remark ?? '-' }}
                </td>
            </tr>
            @endforeach
    </tbody>
</table>