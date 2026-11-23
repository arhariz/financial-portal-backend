<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TransactionDirection;
use App\Enums\WalletTransactionType;
use App\Enums\WalletType;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class WalletService
{
    /**
     * Post a credit to a user's wallet and write the matching immutable ledger row
     * in the same transaction (MON-02, MON-04). Returns the ledger entry.
     *
     * @throws InvalidArgumentException when the amount is not a positive 4dp value
     */
    public function deposit(
        User $user,
        WalletType $type,
        string $amount,
        WalletTransactionType $transactionType = WalletTransactionType::Deposit,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?User $actor = null,
    ): WalletTransaction {
        if ($this->toMinorUnits($amount) <= 0) {
            throw new InvalidArgumentException('Deposit amount must be greater than zero.');
        }

        return DB::transaction(function () use ($user, $type, $amount, $transactionType, $referenceType, $referenceId, $actor): WalletTransaction {
            $wallet = Wallet::firstOrCreate(
                ['user_id' => $user->id, 'type' => $type],
                ['currency' => 'USD', 'balance' => '0.0000']
            );

            $lockedWallet = Wallet::where('id', $wallet->id)->lockForUpdate()->firstOrFail();

            $newBalance = $this->addDecimalStrings((string) $lockedWallet->balance, $amount);

            $transaction = WalletTransaction::create([
                'uuid' => (string) Str::uuid(),
                'wallet_id' => $lockedWallet->id,
                'direction' => TransactionDirection::Credit,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'type' => $transactionType,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'counterparty_wallet_id' => null,
                'created_by' => $actor?->id,
            ]);

            $lockedWallet->balance = $newBalance;
            $lockedWallet->save();

            return $transaction;
        });
    }

    /**
     * Post an atomic debit and immutable ledger row for a money outflow
     * (MON-02/04/05, WAL-03/05). Insufficient funds never produce a negative
     * balance. Returns the ledger entry.
     */
    public function withdraw(
        User $user,
        WalletType $type,
        string $amount,
        WalletTransactionType $transactionType = WalletTransactionType::Withdrawal,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?User $actor = null,
    ): WalletTransaction {
        if ($this->toMinorUnits($amount) <= 0) {
            throw new InvalidArgumentException('Withdrawal amount must be greater than zero.');
        }

        return DB::transaction(function () use ($user, $type, $amount, $transactionType, $referenceType, $referenceId, $actor): WalletTransaction {
            $wallet = Wallet::where('user_id', $user->id)->where('type', $type)->firstOrFail();
            $lockedWallet = Wallet::where('id', $wallet->id)->lockForUpdate()->firstOrFail();

            if ($this->toMinorUnits((string) $lockedWallet->balance) < $this->toMinorUnits($amount)) {
                throw new InvalidArgumentException('Insufficient wallet balance.');
            }

            $newBalance = $this->subtractDecimalStrings((string) $lockedWallet->balance, $amount);

            $transaction = WalletTransaction::create([
                'uuid' => (string) Str::uuid(),
                'wallet_id' => $lockedWallet->id,
                'direction' => TransactionDirection::Debit,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'type' => $transactionType,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'counterparty_wallet_id' => null,
                'created_by' => $actor?->id,
            ]);

            $lockedWallet->balance = $newBalance;
            $lockedWallet->save();

            return $transaction;
        });
    }

    public function walletFor(User $user, WalletType $type): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $user->id, 'type' => $type],
            ['currency' => 'USD', 'balance' => '0.0000']
        );
    }

    private function addDecimalStrings(string $left, string $right): string
    {
        return self::fromMinorUnits(self::toMinorUnits($left) + self::toMinorUnits($right));
    }

    private function subtractDecimalStrings(string $left, string $right): string
    {
        return self::fromMinorUnits(self::toMinorUnits($left) - self::toMinorUnits($right));
    }

    private static function toMinorUnits(string $amount): int
    {
        $negative = str_starts_with($amount, '-');
        $digits = ltrim($amount, '+-');
        [$whole, $fraction] = array_pad(explode('.', $digits, 2), 2, '0');
        $fraction = substr($fraction.'0000', 0, 4);

        $units = ((int) $whole * 10000) + (int) $fraction;

        return $negative ? -$units : $units;
    }

    private static function fromMinorUnits(int $units): string
    {
        $sign = $units < 0 ? '-' : '';
        $absolute = abs($units);

        return sprintf('%s%d.%04d', $sign, intdiv($absolute, 10000), $absolute % 10000);
    }
}
