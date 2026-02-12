# Laravel – Prêt à exécuter

Exécuter ces commandes **dans l’ordre** à la racine du projet.

> Si `composer install` échoue (erreur proxy/réseau type 127.0.0.1:9), désactiver le proxy ou exécuter les commandes depuis un terminal où le réseau fonctionne.

## Windows (PowerShell)

```powershell
# 1. Dépendances
composer install

# 2. Environnement
Copy-Item .env.example .env
php artisan key:generate

# 3. Base de données
New-Item -ItemType File -Path database\database.sqlite -Force
php artisan migrate

# 4. Lien storage
php artisan storage:link

# 5. Utilisateur admin (répondre aux questions)
php artisan make:filament-user

# 6. Lancer le serveur
php artisan serve
```

Puis ouvrir : **http://localhost:8000/admin**

---

## Alternative : script unique

```powershell
.\install.ps1
php artisan make:filament-user
php artisan serve
```

---

## Linux / macOS

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan storage:link
php artisan make:filament-user
php artisan serve
```
