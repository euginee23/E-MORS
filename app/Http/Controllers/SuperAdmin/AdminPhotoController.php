<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminPhotoController extends Controller
{
    public function __invoke(User $admin, string $type): StreamedResponse
    {
        abort_unless(in_array($type, ['valid_id', 'live_photo'], true), 404);

        $path = $type === 'valid_id' ? $admin->valid_id_path : $admin->live_photo_path;

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path);
    }
}
