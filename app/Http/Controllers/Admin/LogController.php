<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;

class LogController extends Controller
{
    public function index()
    {
        $logs = AdminLog::with('admin')
            ->latest()
            ->paginate(15);

        return view('admin.logs.index', compact('logs'));
    }
}