<?php

namespace App\Console\Commands;

use App\Models\User;
use BezhanSalleh\FilamentShield\FilamentShield;
use Filament\Facades\Filament;
use Illuminate\Console\Command;
use Spatie\Permission\PermissionRegistrar;

class AssignSuperAdminUsers extends Command
{
    protected $signature = 'shield:assign-super-admin
        {--panel=admin : Panel ID}';

    protected $description = 'Assigne le rôle super_admin aux utilisateurs silasjmas@gmail.com et xanderssamoth';

    public function handle(): int
    {
        Filament::setCurrentPanel(Filament::getPanel($this->option('panel')));

        $superAdminRole = FilamentShield::createRole();

        $users = User::query()
            ->where('email', 'silasjmas@gmail.com')
            ->orWhere('username', 'xanderssamoth')
            ->get();

        if ($users->isEmpty()) {
            $this->error('Aucun utilisateur trouvé avec email silasjmas@gmail.com ou username xanderssamoth.');

            return self::FAILURE;
        }

        foreach ($users as $user) {
            $user->assignRole($superAdminRole);
            $this->info("Rôle super_admin assigné à : {$user->email} (ID: {$user->id})");
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->info('Terminé.');

        return self::SUCCESS;
    }
}
