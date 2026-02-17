<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; }
        .school-name { font-size: 20px; font-weight: bold; margin-bottom: 5px; }
        .report-title { font-size: 16px; color: #555; text-decoration: underline; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 10px; color: #777; }
        .badge { background: #eee; padding: 2px 5px; border-radius: 3px; font-size: 10px; margin-right: 2px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="school-name">{{ $school->school_name ?? 'School Roster' }}</div>
        <div>{{ $school->address ?? '' }}</div>
        <div>Email: {{ $school->email ?? 'N/A' }} | Phone: {{ $school->phone ?? 'N/A' }}</div>
        <hr>
        <div class="report-title">TEACHER DUTY ROSTER: {{ $monthTitle }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">Week</th>
                <th style="width: 50%;">Assigned Teacher(s)</th>
                <th style="width: 30%;">Dates</th>
                <th style="width: 10%;">Term</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $groupedDuties = $duties->groupBy(function($item) {
                    return $item->start_date . '_' . $item->end_date;
                });
                $weekCounter = 1;
            @endphp
            @foreach($groupedDuties as $key => $weekGroup)
                <tr>
                    <td>Week {{ $weekCounter++ }}</td>
                    <td>
                        @foreach($weekGroup as $duty)
                            {{ $duty->teacher ? $duty->teacher->first_name . ' ' . $duty->teacher->last_name : 'N/A' }}{{ !$loop->last ? ', ' : '' }}
                        @endforeach
                    </td>
                    <td>
                        {{ \Carbon\Carbon::parse($weekGroup[0]->start_date)->format('d M') }} - 
                        {{ \Carbon\Carbon::parse($weekGroup[0]->end_date)->format('d M Y') }}
                    </td>
                    <td>{{ $weekGroup[0]->term ? $weekGroup[0]->term->term_name : 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Printed on: {{ date('d M Y H:i') }}
    </div>
</body>
</html>
