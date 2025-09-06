<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Server Reboot Confirmation</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-6">
        <!-- Header -->
        <div class="text-center mb-6">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-900">Server Down Alert</h1>
            <p class="text-sm text-gray-600">Confirm server restart</p>
        </div>

        <!-- Site Info -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h2 class="font-semibold text-gray-900 mb-2">{{ $site->name }}</h2>
            <p class="text-sm text-gray-600 mb-1">URL: {{ $site->url }}</p>
            <p class="text-sm text-gray-600 mb-1">Droplet: {{ $site->droplet_id }}</p>
            <p class="text-sm text-gray-600">
                Detected: {{ $incident->detected_at->format('H:i d/m/Y') }}
            </p>
        </div>

        <!-- Warning -->
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        This will restart your DigitalOcean server. The process may take 2-3 minutes and will cause temporary downtime.
                    </p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <form method="POST" action="{{ route('reboot.execute', ['site' => $site, 'incident' => $incident]) }}">
            @csrf
            <div class="flex space-x-4">
                <button type="button" 
                        onclick="window.close()" 
                        class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-3 px-4 rounded text-center">
                    Cancel
                </button>
                <button type="submit" 
                        class="flex-1 bg-red-500 hover:bg-red-700 text-white font-bold py-3 px-4 rounded">
                    Restart Server
                </button>
            </div>
        </form>

        <!-- Footer -->
        <div class="mt-6 text-center">
            <p class="text-xs text-gray-500">
                This link expires in {{ now()->addHour()->diffForHumans() }}
            </p>
        </div>
    </div>
</body>
</html>