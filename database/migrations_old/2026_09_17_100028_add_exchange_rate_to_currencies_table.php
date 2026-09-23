<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds exchange_rate_to_base to the currencies table.
 *
 * Convention: exchange_rate_to_base represents the value of 1 BASE currency unit
 * expressed in this currency.
 *
 *   - Base currency: exchange_rate_to_base = 1.0
 *   - Foreign currency X: exchange_rate_to_base = how many units of X equals 1 base currency
 *
 * Example (base = LYD):
 *   USD.exchange_rate_to_base = 0.21 → 1 LYD = 0.21 USD
 *
 * Conversion formula (from → to):
 *   rate = (1.0 / from.exchange_rate_to_base) * to.exchange_rate_to_base
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('currencies', function (Blueprint $table) {
            if (!Schema::hasColumn('currencies', 'exchange_rate_to_base')) {
                /**
                 * Nullable on purpose: a NULL rate on a non-base currency
                 * will trigger ExchangeRateNotFoundException during conversion,
                 * preventing silent incorrect conversions.
                 */
                $table->decimal('exchange_rate_to_base', 15, 6)
                    ->nullable()
                    ->after('base_currency');
            }
        });
    }

    public function down(): void
    {
        Schema::table('currencies', function (Blueprint $table) {
            if (Schema::hasColumn('currencies', 'exchange_rate_to_base')) {
                $table->dropColumn('exchange_rate_to_base');
            }
        });
    }
};
