<?php

declare(strict_types=1);

namespace App\Filament\Resources\WithdrawalRequests\Tables;

use App\Enums\WithdrawalStatus;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Services\WithdrawalService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WithdrawalRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('source_wallet_type')
                    ->badge(),
                TextColumn::make('amount_usd')
                    ->label('USD')
                    ->numeric(decimalPlaces: 4)
                    ->sortable(),
                TextColumn::make('payout_method')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('approver.name')
                    ->label('Reviewed by'),
                TextColumn::make('requested_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('rejection_reason')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('requested_at', 'desc')
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve this withdrawal?')
                    ->visible(fn (): bool => auth()->user()?->can('withdrawals.approve') ?? false)
                    ->hidden(fn (WithdrawalRequest $record): bool => $record->status !== WithdrawalStatus::Pending)
                    ->action(function (WithdrawalRequest $record, WithdrawalService $service): void {
                        $reviewer = auth()->user();

                        if ($reviewer instanceof User) {
                            $service->approve($reviewer, $record);
                        }
                    }),
                Action::make('pay')
                    ->label('Mark paid')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Mark this withdrawal as paid?')
                    ->form([
                        Textarea::make('note')
                            ->label('Payment reference')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->visible(fn (): bool => auth()->user()?->can('withdrawals.approve') ?? false)
                    ->hidden(fn (WithdrawalRequest $record): bool => $record->status !== WithdrawalStatus::Approved)
                    ->action(function (array $data, WithdrawalRequest $record, WithdrawalService $service): void {
                        $reviewer = auth()->user();

                        if ($reviewer instanceof User) {
                            $service->markPaid($reviewer, $record, (string) $data['note']);
                        }
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->form([
                        Textarea::make('reason')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->visible(fn (): bool => auth()->user()?->can('withdrawals.approve') ?? false)
                    ->hidden(fn (WithdrawalRequest $record): bool => $record->status !== WithdrawalStatus::Pending)
                    ->action(function (array $data, WithdrawalRequest $record, WithdrawalService $service): void {
                        $reviewer = auth()->user();

                        if ($reviewer instanceof User) {
                            $service->reject($reviewer, $record, (string) $data['reason']);
                        }
                    }),
            ]);
    }
}
