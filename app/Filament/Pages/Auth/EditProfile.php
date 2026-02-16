<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;
use Illuminate\Support\Facades\Hash;

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
                                TextInput::make('avatar_url')
                                    ->label('URL photo de profil')
                                    ->url()
                                    ->maxLength(500)
                                    ->placeholder('https://...'),
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

        return $data;
    }
}
