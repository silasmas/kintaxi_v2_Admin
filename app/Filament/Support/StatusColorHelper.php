<?php

namespace App\Filament\Support;

/**
 * Couleurs Filament et classes CSS pour distinguer les statuts dans l'admin.
 */
class StatusColorHelper
{
  /**
   * Couleur Filament pour un statut de course.
   */
  public static function rideStatusColor(string $status): string
  {
    return match ($status) {
      'requested' => 'gray',
      'accepted' => 'info',
      'in_progress' => 'warning',
      'completed' => 'success',
      'canceled' => 'danger',
      default => 'gray',
    };
  }

  /**
   * Libellé français d'un statut de course.
   */
  public static function rideStatusLabel(string $status): string
  {
    return match ($status) {
      'requested' => 'Demandée',
      'accepted' => 'Acceptée',
      'in_progress' => 'En cours',
      'completed' => 'Terminée',
      'canceled' => 'Annulée',
      default => $status,
    };
  }

  /**
   * Classes Tailwind pour badge statut course (widgets Blade).
   */
  public static function rideStatusCssClasses(string $status): string
  {
    return match ($status) {
      'requested' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
      'accepted' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
      'in_progress' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
      'completed' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
      'canceled' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
      default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
    };
  }

  /**
   * Couleur Filament pour un nom de statut métier (table status).
   */
  public static function statusNameColor(?string $statusName): string
  {
    if ($statusName === null || $statusName === '') {
      return 'gray';
    }

    $normalized = mb_strtolower($statusName);

    if (
      str_contains($normalized, 'activé')
      || str_contains($normalized, 'confirmé')
      || str_contains($normalized, 'récu')
      || str_contains($normalized, 'validé')
      || str_contains($normalized, 'approuvé')
      || str_contains($normalized, 'terminé')
      || str_contains($normalized, 'payé')
    ) {
      return 'success';
    }

    if (
      str_contains($normalized, 'echoué')
      || str_contains($normalized, 'échoué')
      || str_contains($normalized, 'annulé')
      || str_contains($normalized, 'refusé')
      || str_contains($normalized, 'suspendu')
      || str_contains($normalized, 'rejeté')
    ) {
      return 'danger';
    }

    if (
      str_contains($normalized, 'attente')
      || str_contains($normalized, 'en cours')
      || str_contains($normalized, 'review')
      || str_contains($normalized, 'examen')
    ) {
      return 'warning';
    }

    if (str_contains($normalized, 'créé') || str_contains($normalized, 'nouveau')) {
      return 'info';
    }

    return 'gray';
  }

  /**
   * Couleur Filament pour un rôle utilisateur.
   */
  public static function roleColor(?string $roleName): string
  {
    if ($roleName === null || $roleName === '') {
      return 'gray';
    }

    $normalized = mb_strtolower($roleName);

    return match (true) {
      str_contains($normalized, 'admin') || str_contains($normalized, 'super') => 'danger',
      str_contains($normalized, 'chauffeur') || str_contains($normalized, 'driver') => 'info',
      str_contains($normalized, 'passager') || str_contains($normalized, 'client') || str_contains($normalized, 'passenger') => 'success',
      str_contains($normalized, 'propriétaire') || str_contains($normalized, 'owner') => 'warning',
      default => 'primary',
    };
  }

  /**
   * Couleur Filament pour une méthode de paiement de course.
   */
  public static function paymentMethodColor(string $method): string
  {
    return match ($method) {
      'cash' => 'warning',
      'kintaxi-wallet' => 'success',
      'mobile-money' => 'info',
      'card' => 'primary',
      default => 'gray',
    };
  }

  /**
   * Libellé français d'une méthode de paiement.
   */
  public static function paymentMethodLabel(string $method): string
  {
    return match ($method) {
      'cash' => 'Espèces',
      'kintaxi-wallet' => 'Portefeuille Kintaxi',
      'mobile-money' => 'Mobile Money',
      'card' => 'Carte',
      default => $method,
    };
  }

  /**
   * Couleur Filament pour un type de transaction.
   */
  public static function transactionTypeColor(string $type): string
  {
    return match ($type) {
      'deposit' => 'success',
      'withdrawal' => 'warning',
      'ride_payment' => 'info',
      'commission' => 'primary',
      default => 'gray',
    };
  }
}
