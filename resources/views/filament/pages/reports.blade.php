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
            @livewire(\App\Filament\Widgets\LedgerHealth::class)
        </div>

        @php($drilldown = $this->getDrilldownTransactions())
        @if ($drilldown !== [])
            <div class="rounded border p-4">
                <h3 class="font-semibold">Ledger rows for wallet {{ request()->integer('wallet') }}</h3>
                <table class="w-full text-sm">
                    <thead>
                        <tr>
                            <th class="text-left">ID</th>
                            <th class="text-left">Direction</th>
                            <th class="text-left">Amount</th>
                            <th class="text-left">Type</th>
                            <th class="text-left">Balance after</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($drilldown as $row)
                            <tr>
                                <td>{{ $row['id'] }}</td>
                                <td>{{ $row['direction'] }}</td>
                                <td>{{ $row['amount'] }}</td>
                                <td>{{ $row['type'] }}</td>
                                <td>{{ $row['balance_after'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-filament-panels::page>
