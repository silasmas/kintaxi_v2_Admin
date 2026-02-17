# Configuration Shield - Permissions et Rôles

Shield est intégré pour la gestion des permissions et rôles dans l'admin Filament.

**Important** : Par défaut, le Dashboard et les widgets nécessitent des autorisations. Les utilisateurs sans le rôle `super_admin` doivent avoir les permissions assignées via un rôle (ex. `page_Dashboard`, `widget_StatsOverviewWidget`, etc.).

## Commandes utiles

### 1. Générer les policies et permissions
```bash
php artisan shield:generate --all --panel=admin
```

### 2. Créer un Super Admin
Attribue le rôle `super_admin` (accès total) à un utilisateur :
```bash
# Par ID utilisateur
php artisan shield:super-admin --user=1 --panel=admin

# Ou interactif (si plusieurs utilisateurs)
php artisan shield:super-admin --panel=admin
```

### 3. Générer un seeder (optionnel)
```bash
php artisan shield:seeder --generate --option=permissions_via_roles
```

## Utilisation

1. **Rôles** : Menu « Rôles » dans la section Sécurité
2. **Attribution** : Dans la fiche Utilisateur, section « Rôles & Permissions (Shield) »
3. **Super Admin** : Le rôle `super_admin` bypass toutes les vérifications

## Fichiers modifiés

- `app/Models/User.php` : trait `HasRoles` (Spatie)
- `app/Providers/Filament/AdminPanelProvider.php` : plugin Shield
- `app/Filament/Resources/UserResource.php` : formulaire rôles Shield
- `config/filament-shield.php` : configuration Shield
- `config/permission.php` : configuration Spatie
