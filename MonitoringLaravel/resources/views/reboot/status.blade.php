<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Server Reboot Status</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta http-equiv="refresh" content="30">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-6">
        <!-- Header -->
        <div class="text-center mb-6">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full 
                        @if($rebootLog->status === 'completed') bg-green-100 @elseif($rebootLog->status === 'failed') bg-red-100 @else bg-yellow-100 @endif mb-4">
                @if($rebootLog->status === 'completed')
                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                @elseif($rebootLog->status === 'failed')
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                @else
                    <svg class="h-6 w-6 text-yellow-600 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                @endif
            </div>
            <h1 class="text-xl font-bold text-gray-900">
                @if($rebootLog->status === 'completed')
                    Server Restarted Successfully
                @elseif($rebootLog->status === 'failed')
                    Server Restart Failed
                @else
                    Server Restart In Progress
                @endif
            </h1>
        </div>

        <!-- Site Info -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h2 class="font-semibold text-gray-900 mb-2">{{ $site->name }}</h2>
            <p class="text-sm text-gray-600 mb-1">URL: {{ $site->url }}</p>
            <p class="text-sm text-gray-600 mb-1">Droplet: {{ $rebootLog->droplet_id }}</p>
            <p class="text-sm text-gray-600">
                Started: {{ $rebootLog->initiated_at->format('H:i d/m/Y') }}
            </p>
            @if($rebootLog->completed_at)
                <p class="text-sm text-gray-600">
                    Completed: {{ $rebootLog->completed_at->format('H:i d/m/Y') }}
                </p>
            @endif
        </div>

        <!-- Status Message -->
        <div class="mb-6">
            @if($rebootLog->status === 'initiated')
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                    <p class="text-sm text-blue-700">
                        Restart command has been sent to DigitalOcean. Please wait...
                    </p>
                </div>
            @elseif($rebootLog->status === 'in_progress')
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <p class="text-sm text-yellow-700">
                        Server is restarting. This usually takes 2-3 minutes. The page will auto-refresh.
                    </p>
                </div>
            @elseif($rebootLog->status === 'completed')
                <div class="bg-green-50 border-l-4 border-green-400 p-4">
                    <p class="text-sm text-green-700">
                        Server has been restarted successfully! Your site should be back online.
                    </p>
                </div>
            @elseif($rebootLog->status === 'failed')
                <div class="bg-red-50 border-l-4 border-red-400 p-4">
                    <p class="text-sm text-red-700">
                        Server restart failed. Please check your DigitalOcean dashboard or contact support.
                    </p>
                    @if($rebootLog->error_message)
                        <p class="text-xs text-red-600 mt-2">Error: {{ $rebootLog->error_message }}</p>
                    @endif
                </div>
            @endif
        </div>

        <!-- Actions -->
        <div class="flex space-x-4">
            @if($rebootLog->status === 'completed')
                <a href="{{ $site->url }}" target="_blank" 
                   class="flex-1 bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-4 rounded text-center">
                    Check Site
                </a>
            @endif
            <button type="button" 
                    onclick="window.close()" 
                    class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-4 rounded">
                Close
            </button>
        </div>

        <!-- Auto-refresh notice -->
        @if(!in_array($rebootLog->status, ['completed', 'failed']))
            <div class="mt-4 text-center">
                <p class="text-xs text-gray-500">
                    This page refreshes automatically every 30 seconds
                </p>
            </div>
        @endif
    </div>
</body>
</html>