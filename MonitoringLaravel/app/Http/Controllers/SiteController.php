<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class SiteController extends Controller
{
    public function index(): View
    {
        $sites = Site::with(['incidents' => function($query) {
            $query->latest()->limit(5);
        }, 'rebootLogs' => function($query) {
            $query->latest()->limit(3);
        }])->paginate(15);

        return view('sites.index', compact('sites'));
    }

    public function create(): View
    {
        return view('sites.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => 'required|url|unique:sites,url',
            'droplet_id' => 'required|string|max:255',
            'notification_phone' => 'required|string|max:20',
            'timeout' => 'required|integer|min:5|max:60',
            'check_interval' => 'required|integer|min:60|max:3600',
            'status' => ['required', Rule::in(['active', 'maintenance'])],
        ]);

        Site::create($validated);

        return redirect()->route('sites.index')
            ->with('success', 'Site created successfully.');
    }

    public function show(Site $site): View
    {
        $site->load([
            'incidents' => function($query) {
                $query->latest()->with('rebootLogs');
            },
            'rebootLogs' => function($query) {
                $query->latest()->with('incident');
            }
        ]);

        $uptimeStats = $this->calculateUptime($site);

        return view('sites.show', compact('site', 'uptimeStats'));
    }

    public function edit(Site $site): View
    {

        return view('sites.create', compact('site'));
    }

    public function update(Request $request, Site $site): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'url' => ['required', 'url', Rule::unique('sites', 'url')->ignore($site->id)],
            'droplet_id' => 'required|string|max:255',
            'notification_phone' => 'required|string|max:20',
            'timeout' => 'required|integer|min:5|max:60',
            'check_interval' => 'required|integer|min:60|max:3600',
            'status' => ['required', Rule::in(['active', 'down', 'maintenance'])],
        ]);

        $site->update($validated);

        return redirect()->route('sites.index')
            ->with('success', 'Site updated successfully.');
    }

    public function destroy(Site $site): RedirectResponse
    {
        $site->delete();

        return redirect()->route('sites.index')
            ->with('success', 'Site deleted successfully.');
    }

    public function testConnection(Site $site): RedirectResponse
    {
        try {
            $startTime = microtime(true);


            $response = \Illuminate\Support\Facades\Http::timeout($site->timeout)
                ->get($site->url);


            $responseTime = round((microtime(true) - $startTime) * 1000);
            $site->update(['last_checked_at' => now()]);

            if ($response->successful()) {
                return redirect()->back()->with('success',
                    "Connection successful! Response time: {$responseTime}ms, Status: {$response->status()}");
            } else {

                return redirect()->back()->with('error',
                    "Connection failed. Status: {$response->status()}");
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error',
                "Connection failed: " . $e->getMessage());
        }
    }

    private function calculateUptime(Site $site): array
    {
        $incidents = $site->incidents()
            ->where('detected_at', '>=', now()->subDays(30))
            ->get();

        $totalTime = 30 * 24 * 60;
        $downTime = 0;

        foreach ($incidents as $incident) {
            if ($incident->resolved_at) {
                $downTime += $incident->detected_at->diffInMinutes($incident->resolved_at);
            } else {
                $downTime += $incident->detected_at->diffInMinutes(now());
            }
        }

        $uptime = (($totalTime - $downTime) / $totalTime) * 100;

        return [
            'uptime_percentage' => max(0, min(100, $uptime)),
            'downtime_minutes' => $downTime,
            'total_incidents' => $incidents->count(),
        ];
    }
}
