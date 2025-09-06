<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Incident;
use App\Models\RebootLog;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_sites' => Site::count(),
            'active_sites' => Site::where('status', 'active')->count(),
            'down_sites' => Site::where('status', 'down')->count(),
            'maintenance_sites' => Site::where('status', 'maintenance')->count(),
        ];

        $recentIncidents = Incident::with(['site', 'rebootLogs'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $unresolvedIncidents = Incident::with('site')
            ->whereNull('resolved_at')
            ->orderBy('detected_at', 'desc')
            ->get();

        $recentReboots = RebootLog::with(['site', 'incident'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $monthlyStats = $this->getMonthlyStats();

        return view('dashboard.index', compact(
            'stats',
            'recentIncidents',
            'unresolvedIncidents',
            'recentReboots',
            'monthlyStats'
        ));
    }

    public function incidents(): View
    {
        $incidents = Incident::with(['site', 'rebootLogs'])
            ->orderBy('detected_at', 'desc')
            ->paginate(20);

        return view('dashboard.incidents', compact('incidents'));
    }

    public function rebootLogs(): View
    {
        $rebootLogs = RebootLog::with(['site', 'incident'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('dashboard.reboot-logs', compact('rebootLogs'));
    }

    public function analytics(): View
    {
        $uptimeStats = $this->getUptimeStats();
        $incidentTrends = $this->getIncidentTrends();
        $rebootStats = $this->getRebootStats();

        return view('dashboard.analytics', compact(
            'uptimeStats',
            'incidentTrends',
            'rebootStats'
        ));
    }

    private function getMonthlyStats(): array
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        return [
            'incidents_this_month' => Incident::where('detected_at', '>=', $currentMonth)->count(),
            'incidents_last_month' => Incident::whereBetween('detected_at', [
                $lastMonth,
                $lastMonth->copy()->endOfMonth()
            ])->count(),
            'reboots_this_month' => RebootLog::where('created_at', '>=', $currentMonth)->count(),
            'reboots_last_month' => RebootLog::whereBetween('created_at', [
                $lastMonth,
                $lastMonth->copy()->endOfMonth()
            ])->count(),
        ];
    }

    private function getUptimeStats(): array
    {
        $sites = Site::with(['incidents' => function($query) {
            $query->where('detected_at', '>=', Carbon::now()->subDays(30));
        }])->get();

        $uptimeData = [];

        foreach ($sites as $site) {
            $totalTime = 30 * 24 * 60;
            $downTime = 0;

            foreach ($site->incidents as $incident) {
                if ($incident->resolved_at) {
                    $downTime += $incident->detected_at->diffInMinutes($incident->resolved_at);
                } else {
                    $downTime += $incident->detected_at->diffInMinutes(now());
                }
            }

            $uptime = (($totalTime - $downTime) / $totalTime) * 100;
            
            $uptimeData[] = [
                'site' => $site,
                'uptime' => max(0, min(100, $uptime)),
                'downtime_minutes' => $downTime,
            ];
        }

        return $uptimeData;
    }

    private function getIncidentTrends(): array
    {
        $days = collect();
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = Incident::whereDate('detected_at', $date)->count();
            
            $days->push([
                'date' => $date->format('Y-m-d'),
                'count' => $count,
            ]);
        }

        return $days->toArray();
    }

    private function getRebootStats(): array
    {
        $last30Days = Carbon::now()->subDays(30);

        return [
            'total_reboots' => RebootLog::where('created_at', '>=', $last30Days)->count(),
            'successful_reboots' => RebootLog::where('created_at', '>=', $last30Days)
                ->where('status', 'completed')->count(),
            'failed_reboots' => RebootLog::where('created_at', '>=', $last30Days)
                ->where('status', 'failed')->count(),
            'avg_reboot_time' => RebootLog::where('created_at', '>=', $last30Days)
                ->whereNotNull('completed_at')
                ->get()
                ->avg(function ($log) {
                    return $log->initiated_at->diffInMinutes($log->completed_at);
                }),
        ];
    }
}
