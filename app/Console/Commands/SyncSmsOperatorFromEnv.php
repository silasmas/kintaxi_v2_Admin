<?php

namespace App\Console\Commands;

use App\Models\SmsOperator;
use Illuminate\Console\Command;

/**
 * Synchronise l'opérateur Keccel actif avec les variables SMS du fichier .env.
 */
class SyncSmsOperatorFromEnv extends Command
{
  protected $signature = 'sms:sync-operator';

  protected $description = 'Met à jour l’opérateur SMS actif depuis le fichier .env';

  public function handle(): int
  {
    $url = trim((string) config('services.sms.url'));
    $token = trim((string) config('services.sms.token'));
    $from = trim((string) config('services.sms.from', 'DGRAD'));

    if ($url === '' || $token === '') {
      $this->error('SMS_URL et SMS_TOKEN doivent être définis dans .env (sans espace après =).');

      return self::FAILURE;
    }

    $operator = SmsOperator::query()->where('is_active', true)->first()
      ?? SmsOperator::query()->first();

    if (! $operator) {
      SmsOperator::query()->create([
        'name' => 'Keccel',
        'provider' => 'keccel',
        'send_url' => $url,
        'balance_url' => config('services.sms.balance_url'),
        'delivery_url' => config('services.sms.delivery_url'),
        'token' => $token,
        'sender' => $from,
        'send_method' => 'POST',
        'is_active' => true,
      ]);
      $this->info('Opérateur Keccel créé depuis .env.');

      return self::SUCCESS;
    }

    $operator->update([
      'send_url' => $url,
      'balance_url' => config('services.sms.balance_url'),
      'delivery_url' => config('services.sms.delivery_url'),
      'token' => $token,
      'sender' => $from,
      'is_active' => true,
    ]);

    $this->info('Opérateur « '.$operator->name.' » synchronisé depuis .env.');

    return self::SUCCESS;
  }
}
