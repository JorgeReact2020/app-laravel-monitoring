<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            @if (request()->routeIs('sites.create'))
            {{ __('Ajout d\'un nouveau site') }}
            @else
            {{ __('Modification du site') }} {{ $site->name }}
            @endif
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ request()->routeIs('sites.create') ? route('sites.store') : route('sites.update', $site) }}">
                        @csrf

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Site Name</label>
                                <input type="text" name="name" id="name"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    value="{{ old('name', $site->name ?? '') }}" required>
                                @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- URL -->
                            <div>
                                <label for="url" class="block text-sm font-medium text-gray-700">URL</label>
                                <input type="url" name="url" id="url"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    value="{{ old('url', $site->url ?? '') }}" placeholder="https://example.com" required>
                                @error('url')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Droplet ID -->
                            <div>
                                <label for="droplet_id" class="block text-sm font-medium text-gray-700">DigitalOcean Droplet ID</label>
                                <input type="text" name="droplet_id" id="droplet_id"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    value="{{ old('droplet_id', $site->droplet_id ?? '') }}" required>
                                @error('droplet_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Notification Phone -->
                            <div>
                                <label for="notification_phone" class="block text-sm font-medium text-gray-700">Notification Phone</label>
                                <input type="tel" name="notification_phone" id="notification_phone"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    value="{{ old('notification_phone', $site->notification_phone ?? '') }}" placeholder="+33612345678" required>
                                @error('notification_phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Timeout -->
                            <div>
                                <label for="timeout" class="block text-sm font-medium text-gray-700">Timeout (seconds)</label>
                                <input type="number" name="timeout" id="timeout"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    value="{{ old('timeout', 10) }}" min="5" max="60" required>
                                @error('timeout')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Check Interval -->
                            <div>
                                <label for="check_interval" class="block text-sm font-medium text-gray-700">Check Interval (seconds)</label>
                                <input type="number" name="check_interval" id="check_interval"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    value="{{ old('check_interval', 300) }}" min="60" max="3600" required>
                                @error('check_interval')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="mt-6">
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status" id="status"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="maintenance" {{ old('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                            </select>
                            @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end mt-6 space-x-4">
                            <a href="{{ route('sites.index') }}"
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded">
                                Annuler
                            </a>
                            <button type="submit"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                @if (request()->routeIs('sites.create'))
                                    Ajouter
                                @else
                                    Modifier
                                @endif
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>