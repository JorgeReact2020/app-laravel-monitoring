<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\Incident;
use App\Models\RebootLog;
use App\Jobs\RebootDropletJob;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class RebootController extends Controller
{
    public function show(Request $request, Site $site, Incident $incident): View
    {
        if (!$request->hasValidSignature()) {
            abort(401, 'Invalid or expired link');
        }

        if ($incident->site_id !== $site->id) {
            abort(404, 'Incident not found for this site');
        }

        if ($incident->isResolved()) {
            return view('reboot.already-resolved', compact('site', 'incident'));
        }

        $existingRebootLog = RebootLog::where('incident_id', $incident->id)
            ->where('status', '!=', 'failed')
            ->first();

        if ($existingRebootLog) {
            return view('reboot.already-initiated', compact('site', 'incident', 'existingRebootLog'));
        }

        return view('reboot.confirm', compact('site', 'incident'));
    }

    public function reboot(Request $request, Site $site, Incident $incident): RedirectResponse
    {
        if (!$request->hasValidSignature()) {
            abort(401, 'Invalid or expired link');
        }

        if ($incident->site_id !== $site->id) {
            abort(404, 'Incident not found for this site');
        }

        if ($incident->isResolved()) {
            return redirect()->back()->with('error', 'This incident has already been resolved.');
        }

        if (!$site->droplet_id) {
            return redirect()->back()->with('error', 'No DigitalOcean droplet ID configured for this site.');
        }

        $existingRebootLog = RebootLog::where('incident_id', $incident->id)
            ->where('status', '!=', 'failed')
            ->first();

        if ($existingRebootLog) {
            return redirect()->back()->with('error', 'A reboot has already been initiated for this incident.');
        }

        $rebootLog = RebootLog::create([
            'site_id' => $site->id,
            'incident_id' => $incident->id,
            'droplet_id' => $site->droplet_id,
            'status' => 'initiated',
            'action_type' => 'reboot',
            'initiated_at' => now(),
            'metadata' => [
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
            ],
        ]);

        Log::info("Reboot initiated for site {$site->name}, incident {$incident->id}", [
            'site_id' => $site->id,
            'incident_id' => $incident->id,
            'reboot_log_id' => $rebootLog->id,
        ]);

        RebootDropletJob::dispatch($rebootLog);

        return redirect()->route('reboot.status', [
            'site' => $site,
            'incident' => $incident,
            'rebootLog' => $rebootLog,
        ])->with('success', 'Reboot initiated successfully. Please wait while the server restarts.');
    }

    public function status(Request $request, Site $site, Incident $incident, RebootLog $rebootLog): View
    {
        if (!$request->hasValidSignature()) {
            abort(401, 'Invalid or expired link');
        }

        if ($rebootLog->site_id !== $site->id || $rebootLog->incident_id !== $incident->id) {
            abort(404, 'Reboot log not found for this site and incident');
        }

        return view('reboot.status', compact('site', 'incident', 'rebootLog'));
    }
}
