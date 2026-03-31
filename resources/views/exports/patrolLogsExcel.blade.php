<table>
    <thead>
        <tr>
            <th colspan="2" style="font-weight: bold; background-color: #2c3e50; color: #ffffff; border: 1px solid #1e293b;">Organization</th>
            <th colspan="2" style="font-weight: bold; background-color: #2c3e50; color: #ffffff; border: 1px solid #1e293b;">Log Type</th>
            <th colspan="3" style="font-weight: bold; background-color: #2c3e50; color: #ffffff; border: 1px solid #1e293b;">Date Range</th>
            <th colspan="3" style="font-weight: bold; background-color: #2c3e50; color: #ffffff; border: 1px solid #1e293b;">Generated On</th>
        </tr>
        <tr>
            <td colspan="2" style="background-color: #f1f5f9; border: 1px solid #cbd5e1; font-weight: bold; color: #334155;">{{ $companyName }}</td>
            <td colspan="2" style="background-color: #f1f5f9; border: 1px solid #cbd5e1; font-weight: bold; color: #334155;">{{ ucfirst(str_replace('_', ' ', $logType)) }}</td>
            <td colspan="3" style="background-color: #f1f5f9; border: 1px solid #cbd5e1; font-weight: bold; color: #334155;">{{ $dateRange }}</td>
            <td colspan="3" style="background-color: #f1f5f9; border: 1px solid #cbd5e1; font-weight: bold; color: #334155;">{{ date('d M Y') }}</td>
        </tr>

        <tr>
            <td colspan="10" style="height: 20px;"></td>
        </tr>

        <tr style="background-color: #1e293b;">
            <th style="color: #ffffff; font-weight: bold; border: 1px solid #0f172a; text-align: center; width: 40px;">#</th>
            <th style="color: #ffffff; font-weight: bold; border: 1px solid #0f172a; text-align: center; width: 130px;">Log Type</th>
            <th style="color: #ffffff; font-weight: bold; border: 1px solid #0f172a; text-align: left; width: 280px;">Notes</th>
            <th style="color: #ffffff; font-weight: bold; border: 1px solid #0f172a; text-align: center; width: 160px;">Location</th>
            <th style="color: #ffffff; font-weight: bold; border: 1px solid #0f172a; text-align: left; width: 180px;">Created By</th>
            <th style="color: #ffffff; font-weight: bold; border: 1px solid #0f172a; text-align: center; width: 120px;">Range</th>
            <th style="color: #ffffff; font-weight: bold; border: 1px solid #0f172a; text-align: center; width: 120px;">Beat</th>
            <th style="color: #ffffff; font-weight: bold; border: 1px solid #0f172a; text-align: center; width: 180px;">Created At</th>
            <th style="color: #ffffff; font-weight: bold; border: 1px solid #0f172a; text-align: left; width: 300px;">Photos (URLs)</th>
            <th style="color: #ffffff; font-weight: bold; border: 1px solid #0f172a; text-align: left; width: 400px;">Payload Details</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($logs as $i => $log)
        <tr style="background-color: {{ $i % 2 == 0 ? '#ffffff' : '#f8fafc' }};">
            <td style="text-align: center; vertical-align: top; border: 1px solid #e2e8f0; color: #64748b;">{{ $i + 1 }}</td>
            <td style="text-align: center; vertical-align: top; border: 1px solid #e2e8f0; font-weight: bold; color: #1e293b;">
                {{ ucfirst(str_replace('_', ' ', $log['type'])) }}
            </td>
            <td style="vertical-align: top; border: 1px solid #e2e8f0; color: #475569; wrap-text: true;">
                {{ $log['notes'] ?: '—' }}
            </td>
            <td style="text-align: center; vertical-align: top; border: 1px solid #e2e8f0; color: #2563eb;">
                @if($log['lat'] && $log['lng'])
                {{ $log['lat'] }}, {{ $log['lng'] }}
                @else
                <span style="color: #cbd5e1;">—</span>
                @endif
            </td>
            <td style="vertical-align: top; border: 1px solid #e2e8f0; font-weight: 500;">{{ $log['session']['user']['name'] ?? 'N/A' }}</td>
            <td style="text-align: center; vertical-align: top; border: 1px solid #e2e8f0; color: #64748b;">{{ $log['session']['site']['client_name'] ?? '—' }}</td>
            <td style="text-align: center; vertical-align: top; border: 1px solid #e2e8f0; color: #64748b;">{{ $log['session']['site']['name'] ?? '—' }}</td>
            <td style="text-align: center; vertical-align: top; border: 1px solid #e2e8f0; color: #334155;">
                {{ date('d-m-Y h:i A', strtotime($log['created_at'])) }}
            </td>

            <td style="text-align: left; vertical-align: top; border: 1px solid #e2e8f0; wrap-text: true; color: #2563eb; font-size: 9px;">
                @if(isset($log['media']) && count($log['media']) > 0)
                @foreach($log['media'] as $mIdx => $m)
                {{ 'https://fms.pugarch.in/public/storage/' . $m['path'] }} @if(!$loop->last) {{ "\n" }} @endif
                @endforeach
                @else
                <span style="color: #cbd5e1;">—</span>
                @endif
            </td>

            <td style="vertical-align: top; border: 1px solid #e2e8f0; wrap-text: true; font-size: 10px; color: #475569;">
                @php
                $payload = $log['payload'] ?? [];
                $lines = [];
                @endphp

                @if(is_array($payload) && count($payload) > 0)
                @foreach($payload as $key => $value)
                @if(in_array($key, ['createdAt', 'created_at'])) @continue @endif

                @php
                $label = ucfirst(str_replace(['_', '-'], ' ', preg_replace('/([a-z])([A-Z])/', '$1 $2', $key)));
                $val = is_bool($value) ? ($value ? 'Yes' : 'No') : (is_array($value) ? json_encode($value) : ($value ?? '—'));
                $lines[] = "• {$label}: {$val}";
                @endphp
                @endforeach
                {{ implode("\n", $lines) }}
                @else
                <span style="color: #cbd5e1;">—</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>