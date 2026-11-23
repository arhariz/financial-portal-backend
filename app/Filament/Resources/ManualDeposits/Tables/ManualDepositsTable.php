<?php

declare(strict_types=1);

namespace App\Filament\Resources\ManualDeposits\Tables;

use App\Enums\ManualDepositStatus;
use App\Models\ManualDeposit;
use App\Models\User;
use App\Services\ManualDepositService;
use App\Services\ProofStorageService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ManualDepositsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('amount_bdt')
                    ->label('BDT')
                    ->numeric(decimalPlaces: 4)
                    ->sortable(),
                TextColumn::make('amount_usd')
                    ->label('USD')
                    ->numeric(decimalPlaces: 4)
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->searchable(),
                TextColumn::make('proof_path')
                    ->label('Proof')
                    ->limit(30)
                    ->url(fn (ManualDeposit $record): ?string => app(ProofStorageService::class)->signedViewUrl($record->proof_path))
                    ->openUrlInNewTab(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('reviewer.name')
                    ->label('Reviewed by'),
                TextColumn::make('reviewed_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('rejection_reason')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve this deposit?')
                    ->visible(fn (): bool => auth()->user()?->can('deposits.approve') ?? false)
                    ->hidden(fn (ManualDeposit $record): bool => $record->status !== ManualDepositStatus::Pending)
                    ->action(function (ManualDeposit $record, ManualDepositService $service): void {
                        $reviewer = auth()->user();

                        if ($reviewer instanceof User) {
                            $service->approve($reviewer, $record);
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
                    ->visible(fn (): bool => auth()->user()?->can('deposits.approve') ?? false)
                    ->hidden(fn (ManualDeposit $record): bool => $record->status !== ManualDepositStatus::Pending)
                    ->action(function (array $data, ManualDeposit $record, ManualDepositService $service): void {
                        $reviewer = auth()->user();

                        if ($reviewer instanceof User) {
                            $service->reject($reviewer, $record, (string) $data['reason']);
                        }
                    }),
            ]);
    }
}
