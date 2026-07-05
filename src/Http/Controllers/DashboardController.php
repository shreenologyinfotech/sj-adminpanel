<?php

declare(strict_types=1);

namespace SJ\AdminPanel\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): View
    {
        $usersCount = DB::table('users')->count();
        $rolesCount = DB::table('sj_roles')->count();
        $breadCount = DB::table('sj_bread_configs')->count();
        
        $activities = DB::table('sj_activity_logs')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('sjadmin::dashboard.index', compact('usersCount', 'rolesCount', 'breadCount', 'activities'));
    }
}