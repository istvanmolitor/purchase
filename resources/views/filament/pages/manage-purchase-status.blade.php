<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                {{ __('purchase::common.save_status') }}
            </x-filament::button>
        </div>
    </form>

    @php
        $logs = $this->getLogs();
    @endphp

    @if($logs->count() > 0)
        <div class="mt-8">
            <h2 class="text-lg font-semibold mb-4">
                {{ __('purchase::common.status_history') }}
            </h2>

            <div>
                @foreach($logs as $log)
                    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 mb-4 shadow-sm">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <span class="font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $log->purchaseStatus?->name ?? __('purchase::common.unknown_status') }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                {{ $log->status_changed_at?->format('Y-m-d H:i:s') }}
                            </div>
                        </div>

                        @if($log->user)
                            <div class="text-sm text-gray-600 dark:text-gray-300 mb-2">
                                <span class="font-medium">{{ __('purchase::common.user') }}:</span>
                                {{ $log->user->name }}
                            </div>
                        @endif

                        @if($log->comment)
                            <div class="text-sm text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-gray-700/50 rounded p-3 mt-2">
                                <span class="font-medium">{{ __('purchase::common.comment') }}:</span>
                                <p class="mt-1">{{ $log->comment }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</x-filament-panels::page>
