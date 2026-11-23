<x-filament-panels::page>
    <div class="space-y-4">
        <h2 class="text-lg font-semibold">Reports</h2>
        <p class="text-sm text-gray-500">
            Module health widgets and drill-downs. Read-only by default; Support has view-only access (REP-07).
        </p>

        <div role="tablist" aria-label="Report sections" class="flex flex-wrap gap-2">
            @foreach ($this->getTabs() as $key => $label)
                <button
                    type="button"
                    role="tab"
                    wire:key="report-tab-{{ $key }}"
                    aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                    class="rounded px-3 py-2 text-sm"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        <div role="tabpanel" class="rounded border p-4">
            <p class="text-sm">Overview — module health widgets appear here as their work packages land.</p>
        </div>
    </div>
</x-filament-panels::page>
