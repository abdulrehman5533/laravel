<!DOCTYPE html>
<html>
<head>
    <title>Attendance Report</title>
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 4px; text-align: center; }
        .bg-gray { background-color: #f2f2f2; }
        .text-left { text-align: left; }
        .present { color: green; font-weight: bold; }
        .absent { color: red; }
        .late { color: orange; }
        .header { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Attendance Register - {{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}</h2>
    </div>

    <table>
        <thead>
            <tr class="bg-gray">
                <th class="text-left">Employee</th>
                @foreach($dates as $date)
                    <th>{{ \Carbon\Carbon::parse($date)->format('d') }}</th>
                @endforeach
                <th>P</th>
                <th>A</th>
                <th>L</th>
                <th>OT</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report as $emp)
                <tr>
                    <td class="text-left">
                        {{ $emp['name'] }}<br>
                        <small>{{ $emp['code'] }}</small>
                    </td>
                    @foreach($dates as $date)
                        <td>
                            @php $status = $emp['days'][$date]; @endphp
                            @if($status == 'present') <span class="present">P</span>
                            @elseif($status == 'absent') <span class="absent">A</span>
                            @elseif($status == 'late') <span class="late">L</span>
                            @elseif($status == 'half_day') HD
                            @elseif($status == 'on_leave') OL
                            @else - @endif
                        </td>
                    @endforeach
                    <td class="bg-gray">{{ $emp['summary']['present'] }}</td>
                    <td class="bg-gray">{{ $emp['summary']['absent'] }}</td>
                    <td class="bg-gray">{{ $emp['summary']['late'] }}</td>
                    <td class="bg-gray">{{ $emp['summary']['overtime_hrs'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
