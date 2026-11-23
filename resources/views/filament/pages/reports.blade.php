<x-filament-panels::page>
    <div class="space-y-5">
        {{-- Hero --}}
        <section
            class="relative overflow-hidden rounded-2xl p-6 text-white shadow-sm"
            style="background: linear-gradient(120deg, #78350f 0%, #b45309 45%, #d97706 100%);"
        >
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-amber-200">Admin observability</p>
                    <h2 class="mt-1 text-2xl font-bold tracking-tight">Reports &amp; module health</h2>
                    <p class="mt-2 max-w-2xl text-sm text-amber-50/90">
                        Reconciliation, money flow and module health at a glance. Every figure is computed from live ledger data — no fabricated numbers.
                    </p>
                </div>
                <div class="flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-bold uppercase tracking-wider">
                    <span class="h-2 w-2 rounded-full bg-emerald-300"></span>
                    Live data
                </div>
            </div>
        </section>

        {{-- Tab bar --}}
        <nav
            role="tablist"
            aria-label="Report sections"
            class="flex gap-1 overflow-x-auto rounded-xl border border-gray-200 bg-white p-1 shadow-sm"
        >
            @foreach ($this->getTabs() as $key => $label)
                <button
                    type="button"
                    role="tab"
                    wire:click="setTab('{{ $key }}')"
                    aria-selected="{{ $this->activeTab === $key ? 'true' : 'false' }}"
                    @class([
                        'flex items-center gap-2 whitespace-nowrap rounded-lg px-3 py-2 text-sm font-semibold transition',
                        'text-amber-800' => $this->activeTab === $key,
                        'text-gray-500 hover:bg-gray-50 hover:text-gray-800' => $this->activeTab !== $key,
                    ])
                    @style([
                        'background: #fffbeb; box-shadow: inset 0 -2px 0 #d97706;' => $this->activeTab === $key,
                    ])
                >
                    <x-filament::icon :icon="$this->getTabIcon($key)" class="h-4 w-4" />
                    {{ $label }}
                </button>
            @endforeach
        </nav>

        {{-- Tab content --}}
        <div role="tabpanel" class="space-y-4">
            @if ($this->activeTab === 'overview')
                <div class="grid gap-4">
                    @livewire(\App\Filament\Widgets\LedgerHealth::class)
                    @livewire(\App\Filament\Widgets\LedgerWalletsTable::class)
                </div>
            @elseif ($this->activeTab === 'ledger_wallets')
                @livewire(\App\Filament\Widgets\LedgerWalletsTable::class)
            @else
                <div class="rounded-2xl border border-gray-200 bg-white px-6 py-14 text-center shadow-sm">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-amber-50 text-amber-700">
                        <x-filament::icon :icon="$this->getTabIcon($this->activeTab)" class="h-7 w-7" />
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-gray-900">{{ $this->getTabs()[$this->activeTab] }}</h3>
                    <p class="mx-auto mt-2 max-w-md text-sm text-gray-500">
                        This module's health widget ships with its work package. No figures are shown until live data is available.
                    </p>
                    <div class="mt-5">
                        <x-filament::badge color="gray">Scheduled with its work package</x-filament::badge>
                    </div>
                </div>
            @endif
        </div>

        {{-- Wallet ledger drill-down --}}
        @php($drilldown = $this->getDrilldownTransactions())
        @if ($drilldown !== [])
            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-5 py-3">
                    <h3 class="font-bold text-gray-900">Ledger rows for wallet {{ request()->integer('wallet') }}</h3>
                    <x-filament::badge color="success">Read-only</x-filament::badge>
                </div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-2">ID</th>
                            <th class="px-5 py-2">Direction</th>
                            <th class="px-5 py-2">Amount</th>
                            <th class="px-5 py-2">Type</th>
                            <th class="px-5 py-2">Balance after</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($drilldown as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-2 font-medium text-gray-800">{{ $row['id'] }}</td>
                                <td class="px-5 py-2">
                                    <x-filament::badge :color="$row['direction'] === 'credit' ? 'success' : 'danger'">
                                        {{ $row['direction'] }}
                                    </x-filament::badge>
                                </td>
                                <td class="px-5 py-2 tabular-nums">{{ $row['amount'] }}</td>
                                <td class="px-5 py-2 text-gray-600">{{ $row['type'] }}</td>
                                <td class="px-5 py-2 tabular-nums">{{ $row['balance_after'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif
    </div>
</x-filament-panels::page>
