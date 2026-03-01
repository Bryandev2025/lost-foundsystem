<?php

namespace App\Http\Controllers\API\Logs;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;

class ActivityLogsController extends Controller
{
    public function index()
    {
        $logs = ActivityLog::with('actor:id,name,role')
            ->latest()
            ->paginate(20);

        return response()->json($logs);
    }
}