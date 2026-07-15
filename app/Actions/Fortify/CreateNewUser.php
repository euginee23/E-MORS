<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\AdminStatus;
use App\Models\Market;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'market_name' => ['required', 'string', 'max:255'],
            'market_address' => ['required', 'string', 'max:500'],
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'contact_number' => ['required', 'string', 'max:20'],
            'valid_id' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'live_photo' => ['required', 'file', 'mimes:jpg,jpeg,png', 'max:5120'],
            'credentials' => ['required', 'array', 'min:1'],
            'credentials.*' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ])->validate();

        return DB::transaction(function () use ($input) {
            $market = Market::create([
                'name' => $input['market_name'],
                'address' => $input['market_address'],
            ]);

            $credentialPaths = collect($input['credentials'])
                ->filter(fn ($file) => $file instanceof UploadedFile)
                ->map(fn (UploadedFile $file) => $file->store('admin-credentials', 'local'))
                ->values()
                ->all();

            return User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'role' => 'admin',
                'market_id' => $market->id,
                'status' => AdminStatus::Pending,
                'is_active' => true,
                'credential_paths' => $credentialPaths,
                'contact_number' => $input['contact_number'],
                'valid_id_path' => $input['valid_id']->store('admin-valid-ids', 'local'),
                'live_photo_path' => $input['live_photo']->store('admin-live-photos', 'local'),
            ]);
        });
    }
}
