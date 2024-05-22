@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
@endsection

@section('content')
    <p class=attendance_date>{{ date('Y-m-d') }}</p>


    <table class="attendance__table">
            <tr class="attendance__row">
                <th class="attendance__label">名前</th>
                <th class="attendance__label">勤務開始</th>
                <th class="attendance__label">勤務終了</th>
                <th class="attendance__label">休憩時間</th>
                <th class="attendance__label">労働時間</th>
            </tr>
        @foreach ($attendances as $attendance)
            <tr class="attendance__row">

                <td class="attendance__data">
                    {{ $attendance->user->name }}
                </td>

                <td class="attendance__data">
                    {{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i:s') : 'N/A' }}
                </td>

                <td class="attendance__data">
                    {{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i:s') : 'N/A' }}
                </td>

            @php
                $breakHours = floor($attendance->total_break_time / 3600);
                $breakMinutes = floor(($attendance->total_break_time % 3600) / 60);
                $breakSeconds = $attendance->total_break_time % 60;

                $workHours = floor($attendance->effective_work_time / 3600);
                $workMinutes = floor(($attendance->effective_work_time % 3600) / 60);
                $workSeconds = $attendance->effective_work_time % 60;
            @endphp

                <td class="attendance__data">
                    {{ sprintf('%02d', $breakHours) }}:{{ sprintf('%02d', $breakMinutes) }}:{{ sprintf('%02d', $breakSeconds) }}
                </td>

                <td class="attendance__data">
                    {{ sprintf('%02d', $workHours) }}:{{ sprintf('%02d', $workMinutes) }}:{{ sprintf('%02d', $workSeconds) }}
                </td>
            </tr>
        @endforeach

    </table>
{{ $attendances->links('vendor.pagination.bootstrap-4') }}
@endsection
