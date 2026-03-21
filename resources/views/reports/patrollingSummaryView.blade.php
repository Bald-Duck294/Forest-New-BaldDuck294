@include('includes.report-header')

<div class="container-fluid">

    <table class="table" style="background:#fcd7a9;">
        <tr>
            <th>Organization</th>
            <th>Date Range</th>
            <th>Generated On</th>
        </tr>
        <tr>
            <td>{{ $companyName }}</td>
            <td>{{ $dateRange }}</td>
            <td>{{ date('d M Y') }}</td>
        </tr>
    </table>

    <div class="text-end mb-2">
        <form method="POST" action="{{ route('patrollingSummaryDownload') }}" target="_blank">
            @csrf
            <input type="hidden" name="summary" value="{{ json_encode($summary) }}">
            <input type="hidden" name="dateRange" value="{{ $dateRange }}">

            <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true"
                style="border: 1px solid grey;padding: 3px 8px;border-radius: 50%;">×</button>
            <button type="submit" class="btn btn-danger" name="format" value="pdf">PDF</button>
            <button type="submit" class="btn btn-success" name="format" value="xlsx">Excel</button>
        </form>
    </div>

    <table class="table table-bordered table-striped">
        <thead style="background:#d97979;color:white;">
            <tr>
                <th>Employee</th>
                <th>Range</th>
                <th>Beat</th>
                <th>Total Sessions</th>
                <th>Completed</th>
                <th>Ongoing</th>
                <th>Total Distance (km)</th>
                <th>Avg Distance (km)</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($summary as $s)
                <tr>
                    <td>{{ $s['guard'] }}</td>
                    <td>{{ $s['range'] }}</td>
                    <td>{{ $s['beat'] }}</td>
                    <td>{{ $s['total_sessions'] }}</td>
                    <td>{{ $s['completed'] }}</td>
                    <td>{{ $s['ongoing'] }}</td>
                    <td>{{ $s['total_distance'] }}</td>
                    <td>{{ $s['avg_distance'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>