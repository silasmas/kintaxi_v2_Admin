<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Facades\Filament;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class EditProfile extends BaseEditProfile
{
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        Section::make('Informations personnelles')
                            ->schema([
                                TextInput::make('firstname')
                                    ->label('Prénom')
                                    ->maxLength(255),
                                TextInput::make('lastname')
                                    ->label('Nom')
                                    ->maxLength(255),
                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                TextInput::make('phone')
                                    ->label('Téléphone')
                                    ->tel()
                                    ->maxLength(255),
                                TextInput::make('username')
                                    ->label('Nom d\'utilisateur')
                                    ->maxLength(255),
                                FileUpload::make('avatar')
                                    ->label('Photo de profil')
                                    ->avatar()
                                    ->imageEditor()
                                    ->circleCropper()
                                    ->disk($this->getAvatarDisk())
                                    ->directory('avatars')
                                    ->image()
                                    ->maxSize(2048),
                            ])
                            ->columns(2),
                        Section::make('Modifier le mot de passe')
                            ->description('Laissez vide pour conserver le mot de passe actuel.')
                            ->schema([
                                $this->getPasswordFormComponent(),
                                $this->getPasswordConfirmationFormComponent(),
                            ])
                            ->collapsed(),
                    ])
                    ->operation('edit')
                    ->model($this->getUser())
                    ->statePath('data')
                    ->inlineLabel(! static::isSimple()),
            ),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        unset($data['password'], $data['passwordConfirmation']);

        // Mapper avatar_url vers avatar pour l'affichage (chemin relatif uniquement)
        if (! empty($data['avatar_url']) && ! str_starts_with($data['avatar_url'], 'http')) {
            $data['avatar'] = ltrim($data['avatar_url'], '/');
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['passwordConfirmation']);

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }

        // Convertir le chemin de l'avatar uploadé en URL pour avatar_url
        $avatarPath = $data['avatar'] ?? null;
        if (! empty($avatarPath)) {
            $path = is_array($avatarPath) ? reset($avatarPath) : $avatarPath;
            $disk = $this->getAvatarDisk();
            $data['avatar_url'] = Storage::disk($disk)->url($path);
        }
        unset($data['avatar']);

        return $data;
    }

    protected function getRedirectUrl(): ?string
    {
        // Recharger la page pour afficher la nouvelle photo dans le menu
        return static::getUrl();
    }

    protected function afterSave(): void
    {
        // Rafraîchir l'utilisateur en session pour que le menu affiche la nouvelle photo
        $user = $this->getUser();
        $user->refresh();
        Filament::auth()->setUser($user);
    }

    /**
     * Disque pour les avatars : "public" en local (fichiers accessibles via /storage),
     * sinon le disque Filament configuré (s3_media en prod).
     */
    protected function getAvatarDisk(): string
    {
        $disk = env('FILAMENT_FILESYSTEM_DISK', 's3_media');
        // Le disque "local" stocke dans storage/app/private = non accessible en HTTP
        return $disk === 'local' ? 'public' : $disk;
    }
}
