<table style="border-collapse:collapse;width:100%;border: 1px solid black;">
    <tbody>

        <tr>
            <th colspan="5" style="text-align: center;background-color: #fcd7a9;font-weight:bold;padding:15px;border: 1px solid black; font-size: 22px">
                Employee Absent Report
            </th>
        </tr>

        <tr>
            <th colspan="1" style="text-align:center; background-color:#fcd7a9;font-weight:bold;padding:5px;border:1px solid black;">
                Organization
            </th>
            <th colspan="1" style="text-align:center; background-color:#fcd7a9;font-weight:bold;padding:5px;border:1px solid black;">
                Date Range
            </th>
            <th colspan="1" style="text-align:center; background-color:#fcd7a9;font-weight:bold;padding:5px;border:1px solid black;">
                Report Type
            </th>
            <th colspan="2" style="text-align:center; background-color:#fcd7a9;font-weight:bold;padding:5px;border:1px solid black;">
                Generated On
            </th>
        </tr>

        <tr>
            <td colspan="1" style="text-align:center;background-color:#fcd7a9;padding:5px;border:1px solid black;">
                {{ $companyName }}
            </td>
            <td colspan="1" style="text-align:center;background-color:#fcd7a9;padding:5px;border:1px solid black;">
                {{ $dateRange }}
            </td>
            <td colspan="1" style="text-align:center;background-color:#fcd7a9;padding:5px;border:1px solid black;">
                @if(isset($subType)) {{$subType}} @else N/A @endif
            </td>
            <td colspan="2" style="text-align:center;background-color:#fcd7a9;padding:5px;border:1px solid black;">
                {{ $generatedOn }}
            </td>
        </tr>

        <tr ><td colspan="5" style="padding: 10px;"></td></tr>

        <tr>
            <th style="background-color: #d97979;border:1px solid black;padding:5px;text-align:center;font-weight:bold;">
                Sr No
            </th>
            <th style="background-color: #d97979;border:1px solid black;padding:5px;text-align:center;font-weight:bold;">
                Employee Name
            </th>
            <th style="background-color: #d97979;border:1px solid black;padding:5px;text-align:center;font-weight:bold;">
                Client
            </th>
            <th style="background-color: #d97979;border:1px solid black;padding:5px;text-align:center;font-weight:bold;">
                Site
            </th>
            <th style="background-color: #d97979;border:1px solid black;padding:5px;text-align:center;font-weight:bold;">
                Role
            </th>
        </tr>

        <?php $srNo = 0; ?>
        @foreach ($data as $var)
        <tr>
            <td style="border:1px solid black;text-align:center;">{{ ++$srNo }}</td>
            <td style="border:1px solid black;">{{ $var['name'] }}</td>
            <td style="border:1px solid black;">{{ $var->client_name }}</td>
            <td style="border:1px solid black;">{{ $var->site_name }}</td>
            @if ($var->role_id == 2)
                <td style="border:1px solid black;">Supervisor</td>
            @else
                <td style="border:1px solid black;">Employee</td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>