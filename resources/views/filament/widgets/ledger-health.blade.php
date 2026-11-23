<div class="rounded border p-4 space-y-3">
    <div class="flex items-center justify-between">
        <h3 class="font-semibold">Ledger Health</h3>
        <span class="rounded px-2 py-1 text-xs font-semibold">{{ strtoupper($this->state()) }}</span>
    </div>

    <p class="text-sm">
        Wallets checked: {{ count($this->result()['wallets']) }} | Breaks: {{ $this->result()['breakCount'] }}
    </p>
    <p class="text-xs text-gray-500">
        Checked at: {{ $this->result()['checkedAt'] }} &middot; Owner: {{ $this->owner() }}
    </p>

    @if ($this->result()['wallets'] === [])
        <p class="text-sm text-gray-500">No ledger data available.</p>
    @else
        <table class="w-full text-sm">
            <thead>
                <tr>
                    <th class="text-left">Wallet</th>
                    <th class="text-left">Type</th>
                    <th class="text-left">Balance</th>
                    <th class="text-left">Expected</th>
                    <th class="text-left">Difference</th>
                    <th class="text-left">Status</th>
                    <th class="text-left">Drill-down</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($this->result()['wallets'] as $row)
                    <tr>
                        <td>{{ $row['id'] }}</td>
                        <td>{{ $row['type'] }}</td>
                        <td>{{ $row['balance'] }}</td>
                        <td>{{ $row['expected'] }}</td>
                        <td>{{ $row['difference'] }}</td>
                        <td>{{ $row['inBreak'] ? 'BREAK' : 'OK' }}</td>
                        <td>
                            <a href="{{ url('/admin/reports?tab=ledger_wallets&wallet='.$row['id']) }}">View ledger rows</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
