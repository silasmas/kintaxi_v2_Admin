# Dikitivi Admin

Projet Laravel 11 avec **Filament** (admin) et stockage **AWS S3** pour les images et vidéos lourdes.

## Prêt à exécuter

À la racine du projet, dans l’ordre :

1. `composer install`
2. Copier `.env.example` vers `.env` puis `php artisan key:generate`
3. Créer la base : `touch database/database.sqlite` (ou `New-Item database\database.sqlite` sous Windows)
4. `php artisan migrate`
5. `php artisan storage:link`
6. `php artisan make:filament-user` (créer un admin)
7. `php artisan serve` → ouvrir **http://localhost:8000/admin**

Voir **EXECUTER.md** pour les commandes détaillées (Windows / Linux).

## Prérequis

- PHP 8.2+
- Composer
- Extension PHP : `pdo`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`
- (Optionnel) Node.js pour les assets

## Installation

> **Note :** Si `composer install` échoue (erreur réseau/proxy), vérifiez votre connexion ou désactivez le proxy, puis réessayez.

### 1. Dépendances PHP

```bash
composer install
```

### 2. Environnement

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Base de données

```bash
touch database/database.sqlite
php artisan migrate
```

### 4. Utilisateur admin Filament

```bash
php artisan make:filament-user
```

Renseignez nom, email et mot de passe pour vous connecter à `/admin`.

### 5. Lien storage (optionnel en local)

```bash
php artisan storage:link
```

### 6. (Windows) Script d’installation

Depuis PowerShell, à la racine du projet :

```powershell
.\install.ps1
```

Puis : `php artisan make:filament-user` et `php artisan serve`.

### 7. Lancer l’application

```bash
php artisan serve
```

- Site : http://localhost:8000  
- Admin Filament : http://localhost:8000/admin  

---

## AWS S3 (images et vidéos)

### Configuration

Dans `.env` :

```env
AWS_ACCESS_KEY_ID=votre_clé
AWS_SECRET_ACCESS_KEY=votre_secret
AWS_DEFAULT_REGION=eu-west-1
AWS_BUCKET=nom-du-bucket
# Optionnel : URL personnalisée (CloudFront, etc.)
# AWS_URL=https://xxx.cloudfront.net
```

### Utilisation

- **Disque par défaut** : `s3` (configuré dans `config/filesystems.php`).
- **Disque médias** : `s3_media` (préfixe `media/` dans le bucket).

Exemple dans un **Resource Filament** :

```php
use App\Services\MediaStorageService;
use Filament\Forms\Components\FileUpload;

// Dans le formulaire
FileUpload::make('image')
    ->disk('s3_media')
    ->directory('images')
    ->image(),

FileUpload::make('video')
    ->disk('s3_media')
    ->directory('videos')
    ->maxSize(512000), // 512 Mo
```

Exemple avec le **service** :

```php
use App\Services\MediaStorageService;

$media = app(MediaStorageService::class);
$path = $media->storeImage($request->file('image'));
$url = $media->url($path);
```

### Filament et disque d’upload

En production, utilisez S3 pour les uploads Filament :

```env
FILAMENT_FILESYSTEM_DISK=s3_media
```

---

## Resource Médias (exemple S3)

Une resource Filament **Médias** est fournie pour gérer images et vidéos sur S3 :

- **Modèle** : `App\Models\Media` (table `media`)
- **Resource** : `App\Filament\Resources\MediaResource`
- **Menu** : Admin → Contenu → Médias

Après `php artisan migrate`, vous pouvez créer / éditer des médias ; les fichiers sont enregistrés sur le disque défini par `FILAMENT_FILESYSTEM_DISK` (`s3_media` ou `public` en local).

## Traductions Filament (français)

Pour publier les fichiers de traduction Filament en français :

```bash
php artisan vendor:publish --tag=filament-panels-translations
```

Puis définir la locale dans `config/app.php` : `'locale' => 'fr'` (déjà le cas dans ce projet).

## Structure utile

| Dossier / Fichier        | Rôle                          |
|-------------------------|-------------------------------|
| `app/Providers/Filament/AdminPanelProvider.php` | Configuration du panel admin |
| `app/Services/MediaStorageService.php`        | Stockage images/vidéos S3   |
| `app/Filament/Resources/MediaResource.php`   | CRUD Médias (exemple S3)   |
| `app/Models/Media.php`  | Modèle pour images/vidéos  |
| `config/filesystems.php`                      | Disques `s3` et `s3_media`  |
| `app/Models/User.php`   | Implémente `FilamentUser`   |

---

## Commandes utiles

```bash
# Créer un admin Filament
php artisan make:filament-user

# Créer une resource Filament (CRUD)
php artisan make:filament-resource NomDuModele --generate

# Optimiser Filament (prod)
php artisan filament:optimize
```

---

## Licence

MIT
