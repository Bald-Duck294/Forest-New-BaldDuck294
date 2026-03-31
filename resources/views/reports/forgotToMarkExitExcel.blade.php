<table style="width: 100%; border-collapse: collapse;">
    <thead>
        <tr style="background-color: #1e293b;">
            <th colspan="1" style="color: #94a3b8; text-align: center; border: 1px solid #334155; font-weight: bold; font-size: 10px;">ORGANIZATION</th>
            <th colspan="2" style="color: #94a3b8; text-align: center; border: 1px solid #334155; font-weight: bold; font-size: 10px;">SITE / BEAT</th>
            <th colspan="2" style="color: #94a3b8; text-align: center; border: 1px solid #334155; font-weight: bold; font-size: 10px;">DATE RANGE</th>
            <th colspan="1" style="color: #94a3b8; text-align: center; border: 1px solid #334155; font-weight: bold; font-size: 10px;">GENERATED ON</th>
        </tr>
        <tr>
            <td colspan="1" style="background-color: #1e293b; color: #ffffff; text-align: center; border: 1px solid #334155; font-weight: bold;">{{ $companyName->name ?? $companyName }}</td>
            <td colspan="2" style="background-color: #1e293b; color: #ffffff; text-align: center; border: 1px solid #334155; font-weight: bold;">{{ ($geofences == 'all') ? 'All site' : $siteName }}</td>
            <td colspan="2" style="background-color: #1e293b; color: #ffffff; text-align: center; border: 1px solid #334155; font-weight: bold;">{{ $startDate }} to {{ $endDate }}</td>
            <td colspan="1" style="background-color: #1e293b; color: #ffffff; text-align: center; border: 1px solid #334155; font-weight: bold;">{{ $generatedOn }}</td>
        </tr>

        <tr>
            <td colspan="6" style="height: 20px; border: none;"></td>
        </tr>

        <tr style="background-color: #334155;">
            <th style="border: 1px solid #000000; color: #ffffff; font-weight: bold; text-align: center; background-color: #334155;">SR. NO.</th>
            <th style="border: 1px solid #000000; color: #ffffff; font-weight: bold; text-align: center; background-color: #334155;">DATE</th>
            <th style="border: 1px solid #000000; color: #ffffff; font-weight: bold; text-align: center; background-color: #334155;">NAME OF EMPLOYEE</th>
            <th style="border: 1px solid #000000; color: #ffffff; font-weight: bold; text-align: center; background-color: #334155;">CLIENT/RANGE</th>
            <th style="border: 1px solid #000000; color: #ffffff; font-weight: bold; text-align: center; background-color: #334155;">SITE</th>
            <th style="border: 1px solid #000000; color: #ffffff; font-weight: bold; text-align: center; background-color: #334155;">PUNCH-IN TIME</th>
        </tr>
    </thead>
    <tbody>
        @foreach($data as $index => $row)
        <tr>
            <td style="border: 1px solid #cccccc; text-align: center;">{{ $index + 1 }}</td>

            {{-- Column 2: Date --}}
            <td style="border: 1px solid #cccccc; text-align: center;">{{ $row->date ?? $row->dateFormat ?? '-' }}</td>

            {{-- Column 3: Name (Checking all possible sources) --}}
            <td style="border: 1px solid #cccccc; text-align: left; font-weight: bold;">
                {{ $row->site_assigned_name ?? $row->user_name ?? $row->name ?? '-' }}
            </td>

            {{-- Column 4: Client/Range --}}
            <td style="border: 1px solid #cccccc; text-align: center;">
                {{ $row->site_assigned_client ?? $row->client_name ?? '-' }}
            </td>

            {{-- Column 5: Site --}}
            <td style="border: 1px solid #cccccc; text-align: left;">
                {{ $row->site_assigned_location ?? $row->site_name ?? '-' }}
            </td>

            {{-- Column 6: Punch-in Time --}}
            <td style="border: 1px solid #cccccc; text-align: center; font-weight: bold;">
                @php
                $time = $row->entry_time ?? $row->punch_in_time ?? $row->in_time ?? null;
                @endphp
                {{ $time ? date('h:i a', strtotime($time)) : '-' }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>