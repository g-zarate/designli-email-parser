<?php

namespace App\Http\Services;


use Illuminate\Http\UploadedFile;

class FileStorageService
{
    public function uploadFile(UploadedFile $file): string|null
    {
        $filePath = $file->store('emails');
        return $filePath;
    }
}
