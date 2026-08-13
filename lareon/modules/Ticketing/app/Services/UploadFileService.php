<?php

namespace Lareon\Modules\Ticketing\App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class UploadFileService
{
    public function __construct(protected string $disk = 'public') {}

    public function store(UploadedFile $file, int|string|null $userId = null): string
    {
        $userId ??= auth()->id();

        if (!$userId) throw new RuntimeException('User ID is required.');

        $path = Storage::disk($this->disk)->put("tickets/{$userId}", $file);

        return Storage::disk($this->disk)->url($path);
    }


    protected function directory(int|string $userId): string
    {
        return "tickets/{$userId}";
    }
}
