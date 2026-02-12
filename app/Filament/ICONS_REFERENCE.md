# Icônes Filament (Heroicons) – Référence

Les ressources Filament utilisent le set **heroicons** (Blade UI). Le préfixe est `heroicon-o-` (outline).  
**Seuls les noms listés ci-dessous existent** dans le package. Tout autre nom provoquera `SvgNotFound`.

## Icônes utilisées dans ce projet (toutes valides)

| Ressource | Icône |
|-----------|--------|
| Country | `heroicon-o-globe-alt` |
| Status | `heroicon-o-flag` |
| UserRole | `heroicon-o-shield-check` |
| Currency | `heroicon-o-currency-dollar` |
| User | `heroicon-o-users` |
| VehicleShape | `heroicon-o-squares-2x2` |
| VehicleCategory | `heroicon-o-table-cells` |
| Vehicle | `heroicon-o-truck` |
| VehicleFeature | `heroicon-o-sparkles` |
| PaymentGateway | `heroicon-o-building-library` |
| PaymentMethod | `heroicon-o-credit-card` |
| Payment | `heroicon-o-banknotes` |
| Transaction | `heroicon-o-arrows-right-left` |
| Ride | `heroicon-o-map-pin` |
| Review | `heroicon-o-star` |
| Document | `heroicon-o-document-text` |
| FileModel | `heroicon-o-document-duplicate` |
| AppNotification | `heroicon-o-bell` |
| PricingRule | `heroicon-o-calculator` |
| PasswordReset | `heroicon-o-key` |
| Media | `heroicon-o-photo` |

## À éviter

- `heroicon-o-view-grid` → **n’existe pas**. Utiliser `heroicon-o-table-cells` ou `heroicon-o-squares-2x2`.
- `heroicon-o-arrow-right-left` → **n’existe pas**. Utiliser `heroicon-o-arrows-right-left` (avec un **s**).

Pour une nouvelle ressource, choisir une icône parmi celles utilisées ci-dessus ou vérifier sa présence dans `vendor/blade-ui-kit/blade-heroicons/resources/svg/` (fichiers `o-*.svg`).
