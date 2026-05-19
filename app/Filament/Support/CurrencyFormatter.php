<?php

namespace App\Filament\Support;

use App\Models\Currency;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Cache;

/**
 * Affiche les montants en USD (stockage métier en CDF) avec équivalent CDF au survol.
 */
class CurrencyFormatter
{
  private const CACHE_KEY = 'kintaxi_currency_rates';

  private const CACHE_TTL = 300;

  /**
   * @return array{usd: float, cdf: float}
   */
  public static function rates(): array
  {
    return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function (): array {
      $usd = (float) (Currency::query()->where('currency_acronym', 'USD')->value('rating') ?? 1);
      $cdf = (float) (Currency::query()->where('currency_acronym', 'CDF')->value('rating') ?? 2200);

      if ($usd <= 0) {
        $usd = 1;
      }
      if ($cdf <= 0) {
        $cdf = 2200;
      }

      return ['usd' => $usd, 'cdf' => $cdf];
    });
  }

  /**
   * Convertit un montant CDF en USD.
   */
  public static function cdfToUsd(?float $amountCdf): ?float
  {
    if ($amountCdf === null) {
      return null;
    }

    $rates = self::rates();

    return round($amountCdf / $rates['cdf'], 2);
  }

  /**
   * Convertit un montant USD en CDF (pour info-bulle).
   */
  public static function usdToCdf(?float $amountUsd): ?float
  {
    if ($amountUsd === null) {
      return null;
    }

    $rates = self::rates();

    return round($amountUsd * $rates['cdf'], 0);
  }

  /**
   * Affichage principal : montant en USD.
   */
  public static function formatUsd(?float $amountCdf): string
  {
    if ($amountCdf === null) {
      return '—';
    }

    $usd = self::cdfToUsd($amountCdf);

    return number_format($usd, 2, ',', ' ').' USD';
  }

  /**
   * Texte au survol : USD et équivalent CDF.
   */
  public static function formatTooltip(?float $amountCdf): ?string
  {
    if ($amountCdf === null) {
      return null;
    }

    $usd = self::cdfToUsd($amountCdf);

    return number_format($usd, 2, ',', ' ').' USD · '
      .number_format($amountCdf, 0, ',', ' ').' CDF';
  }

  /**
   * @deprecated Utiliser formatUsd() + formatTooltip()
   */
  public static function formatDual(?float $amountCdf): string
  {
    return self::formatUsd($amountCdf);
  }

  /**
   * Configure une colonne tableau Filament (USD + tooltip CDF).
   */
  public static function configureMoneyColumn(TextColumn $column): TextColumn
  {
    return $column
      ->formatStateUsing(fn ($state): string => self::formatUsd($state !== null ? (float) $state : null))
      ->tooltip(fn ($state): ?string => self::formatTooltip($state !== null ? (float) $state : null));
  }

  /**
   * Configure une entrée infolist Filament (USD + tooltip CDF).
   */
  public static function configureMoneyEntry(TextEntry $entry): TextEntry
  {
    return $entry
      ->formatStateUsing(fn ($state): string => self::formatUsd($state !== null ? (float) $state : null))
      ->tooltip(fn ($state): ?string => self::formatTooltip($state !== null ? (float) $state : null));
  }

  /**
   * Efface le cache des taux (après mise à jour des devises).
   */
  public static function clearCache(): void
  {
    Cache::forget(self::CACHE_KEY);
  }
}
