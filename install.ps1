# Script d'installation - Dikitivi Admin (PowerShell)
# Exécuter : .\install.ps1

$ErrorActionPreference = "Stop"

Write-Host "=== Installation Dikitivi Admin ===" -ForegroundColor Cyan

if (-not (Test-Path "composer.json")) {
    Write-Host "Erreur : exécutez ce script depuis la racine du projet." -ForegroundColor Red
    exit 1
}

Write-Host "`n1. Installation des dépendances Composer..." -ForegroundColor Yellow
composer install --no-interaction
if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

if (-not (Test-Path ".env")) {
    Write-Host "`n2. Création du fichier .env..." -ForegroundColor Yellow
    Copy-Item ".env.example" ".env"
    php artisan key:generate
} else {
    Write-Host "`n2. Fichier .env déjà présent." -ForegroundColor Green
}

if (-not (Test-Path "database\database.sqlite")) {
    Write-Host "`n3. Création de la base SQLite et migrations..." -ForegroundColor Yellow
    New-Item -ItemType File -Path "database\database.sqlite" -Force
    php artisan migrate --force
} else {
    Write-Host "`n3. Base de données existante. Exécution des migrations..." -ForegroundColor Yellow
    php artisan migrate --force
}

Write-Host "`n4. Lien storage (public/storage)..." -ForegroundColor Yellow
if (-not (Test-Path "public\storage")) {
    php artisan storage:link
}

Write-Host "`n=== Installation terminée ===" -ForegroundColor Green
Write-Host "`nCréez un utilisateur admin : php artisan make:filament-user" -ForegroundColor Cyan
Write-Host "Puis lancez le serveur : php artisan serve" -ForegroundColor Cyan
Write-Host "Admin : http://localhost:8000/admin" -ForegroundColor Cyan
