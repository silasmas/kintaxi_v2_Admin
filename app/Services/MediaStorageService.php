<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaStorageService
{
    public function __construct(
        protected string $disk = 's3_media'
    ) {}

    /**
     * Stocke une image sur S3 (ou le disque configuré).
     */
    public function storeImage(UploadedFile $file, string $directory = 'images'): string
    {
        return $this->store($file, $directory);
    }

    /**
     * Stocke une vidéo sur S3 (idéal pour les fichiers lourds).
     */
    public function storeVideo(UploadedFile $file, string $directory = 'videos'): string
    {
        return $this->store($file, $directory);
    }

    /**
     * Stocke un fichier et retourne le chemin.
     */
    public function store(UploadedFile $file, string $directory = 'media'): string
    {
        $name = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $directory . '/' . date('Y/m/d') . '/' . $name;

        Storage::disk($this->disk)->put($path, file_get_contents($file->getRealPath()), 'public');

        return $path;
    }

    /**
     * Retourne l’URL publique du fichier (si le bucket le permet).
     */
    public function url(string $path): ?string
    {
        return Storage::disk($this->disk)->url($path);
    }

    /**
     * Supprime un fichier.
     */
    public function delete(string $path): bool
    {
        return Storage::disk($this->disk)->delete($path);
    }

    /**
     * Change le disque (ex: 's3_media' en prod, 'public' en local).
     */
    public function disk(string $disk): self
    {
        $this->disk = $disk;
        return $this;
    }
}
