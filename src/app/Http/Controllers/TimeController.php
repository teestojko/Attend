<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;
// use Illuminate\Support\Carbon;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Date;

class TimeController extends Controller
{


    public function index()
    {
        $users = User::with('attendances.rests')->get();
        dd($users);

        return view('index', compact('users'));
    }




    public function store(Request $request)
    {
    $action = $request->input('action');//form button ravel のname"" と('')の記述を合わせる

        switch ($action) {

        case 'clock_in':
            $user = Auth::user();
            $existingClockIn = $user->attendances()->where('date', Carbon::today())->whereNotNull('clock_in')->exists();
            if ($existingClockIn) {
                return back()->with('error', '既に出勤されています');
            } else {
                $user->attendances()->create([
                    'date' => Carbon::now()->toDateString(),
                    'clock_in' => Carbon::now()->format('H:i:s'),
                ]);
                return back()->with('error', 'おはようございます！');
            }
            break;


        case 'clock_out':
            $user = Auth::user();
            $today = Carbon::today()->toDateString();
            $existingAttendance = $user->attendances()->where('date', $today)->first();
        if ($existingAttendance) {
            if (is_null($existingAttendance->clock_out)) {
                $existingAttendance->update([
                    'clock_out' => Carbon::now()->format('H:i:s'),
                ]);
                return back()->with('message', 'お疲れ様でした！');
            } else {
                return back()->with('error', '既に退勤されています');
            }
        } else {
            return back()->with('error', 'まだ出勤されていません');
        }
                break;



        case 'break_in':
    $user = Auth::user();
    $attendance = $user->attendances()->where('date', Carbon::today())->first();

    if ($attendance) {
        if ($attendance->clock_out) {
            return back()->with('error', '勤務終了後に休憩開始はできません');
        }

        $latestRest = $attendance->rests()->latest()->first();
        if (!$latestRest || ($latestRest && $latestRest->break_out)) {
            $attendance->rests()->create([
                'break_in' => Carbon::now()->format('H:i:s'),
            ]);
        } else {
            return back()->with('error', '最新の休憩が終了していないか、休憩が開始されていません');
        }
    } else {
        return back()->with('error', 'まだ出勤していません');
    }
    break;


        case 'break_out':
        $user = Auth::user();
        $attendance = $user->attendances()->where('date', Carbon::today())->first();
        if ($attendance) {
            if ($attendance->clock_in && is_null($attendance->clock_out)) {
                $latestRest = $attendance->rests()->latest()->first();
                if ($latestRest && $latestRest->break_in && is_null($latestRest->break_out)) {
                    $latestRest->update([
                        'break_out' => Carbon::now()->format('H:i:s'),
                    ]);
                    return back()->with('message', '無理せず頑張りましょう！');
                } else {
                    return back()->with('error', 'もう休憩終わってますよ！！');
                }
            } else {
                return back()->with('error', '既に退勤が押されています');
            }
        } else {
            return back()->with('error', 'まだ出勤が押されていません');
        }
        break;
    }


    return back();
    }




public function attendance(Request $request)
{
    $today = Carbon::now()->toDateString();

    $attendances = Attendance::with('user', 'rests')
        ->whereDate('date', $today)
        ->get();

    foreach ($attendances as $attendance) {
        if ($attendance->clock_in && $attendance->clock_out) {
            $attendance->total_break_time = $this->calculateTotalBreakTime($attendance);
            $attendance->effective_work_time = $this->calculateEffectiveWorkTime($attendance);
        } else {
            $attendance->total_break_time = 0;
            $attendance->effective_work_time = 0;
        }
    }

    return view('attendance', compact('attendances'));
}

private function calculateTotalBreakTime($attendance)
{
    $totalBreakTime = 0;
    $lastRestEnd = null;

    foreach ($attendance->rests as $rest) {
        if ($rest->break_in && $lastRestEnd !== null) {
            $totalBreakTime += $rest->break_in->diffInSeconds($lastRestEnd);
        }
        if ($rest->break_out) {
            $lastRestEnd = $rest->break_out;
        }
    }

    return $totalBreakTime;
}

private function calculateEffectiveWorkTime($attendance)
{
    if ($attendance->clock_in && $attendance->clock_out) {
        $totalWorkTime = $attendance->clock_out->diffInSeconds($attendance->clock_in);
        $totalBreakTime = $this->calculateTotalBreakTime($attendance);

        return $totalWorkTime - $totalBreakTime;
    }

    return 0; // デフォルトで0を返す、または適切なデフォルト値を返す
}
}
