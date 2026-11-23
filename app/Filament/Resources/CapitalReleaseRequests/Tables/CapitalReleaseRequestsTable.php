<?php

declare(strict_types=1);

namespace App\Filament\Resources\CapitalReleaseRequests\Tables;

use App\Enums\CapitalReleaseStatus;
use App\Models\CapitalReleaseRequest;
use App\Models\User;
use App\Services\CapitalReleaseService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CapitalReleaseRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->searchable(),
                TextColumn::make('amount_usd')
                    ->label('USD')
                    ->numeric(decimalPlaces: 4)
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('approver.name')
                    ->label('Approved by'),
                TextColumn::make('requested_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('release_at')
                    ->dateTime()
                    ->label('Release at (24h)')
                    ->sortable(),
                TextColumn::make('released_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('requested_at', 'desc')
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve this capital release? The 24h timer starts now.')
                    ->visible(fn (): bool => auth()->user()?->can('capital_releases.approve') ?? false)
                    ->hidden(fn (CapitalReleaseRequest $record): bool => $record->status !== CapitalReleaseStatus::Pending)
                    ->action(function (CapitalReleaseRequest $record, CapitalReleaseService $service): void {
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
                    ->visible(fn (): bool => auth()->user()?->can('capital_releases.approve') ?? false)
                    ->hidden(fn (CapitalReleaseRequest $record): bool => $record->status !== CapitalReleaseStatus::Pending)
                    ->action(function (array $data, CapitalReleaseRequest $record, CapitalReleaseService $service): void {
                        $reviewer = auth()->user();

                        if ($reviewer instanceof User) {
                            $service->reject($reviewer, $record, (string) $data['reason']);
                        }
                    }),
            ]);
    }
}
