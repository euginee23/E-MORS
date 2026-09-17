<?php

use App\Enums\AdminStatus;
use App\Enums\UserRole;
use App\Models\Market;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
    Storage::fake('local');

    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'market_name' => 'Test Market',
        'market_address' => '123 Test Street',
        'contact_number' => '09171234567',
        'valid_id' => UploadedFile::fake()->image('valid-id.jpg'),
        'live_photo' => UploadedFile::fake()->image('live-photo.jpg'),
        'credentials' => [UploadedFile::fake()->create('appointment.pdf', 100, 'application/pdf')],
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();

    $user = User::where('email', 'test@example.com')->firstOrFail();

    expect($user->role)->toBe(UserRole::Admin)
        // A new market admin is not trusted until a Super Admin reviews them.
        ->and($user->status)->toBe(AdminStatus::Pending)
        ->and($user->is_active)->toBeTrue()
        ->and($user->contact_number)->toBe('09171234567')
        ->and($user->valid_id_path)->not->toBeNull()
        ->and($user->live_photo_path)->not->toBeNull()
        ->and($user->credential_paths)->toHaveCount(1);

    expect(Market::where('name', 'Test Market')->exists())->toBeTrue();

    Storage::disk('local')->assertExists($user->valid_id_path);
    Storage::disk('local')->assertExists($user->live_photo_path);
    Storage::disk('local')->assertExists($user->credential_paths[0]);
});

test('registration requires identity documents', function () {
    $response = $this->post(route('register.store'), [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'market_name' => 'Test Market',
        'market_address' => '123 Test Street',
    ]);

    $response->assertSessionHasErrors(['contact_number', 'valid_id', 'live_photo', 'credentials']);

    $this->assertGuest();
    expect(User::where('email', 'test@example.com')->exists())->toBeFalse();
    expect(Market::where('name', 'Test Market')->exists())->toBeFalse();
});

test('a pending admin is held on the waiting screen until verified', function () {
    $admin = User::factory()->pending()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertRedirect(route('admin.pending'));

    $admin->update(['status' => AdminStatus::Verified]);

    $this->actingAs($admin->fresh())
        ->get(route('dashboard'))
        ->assertOk();
});

test('a rejected or deactivated admin is logged out', function () {
    $rejected = User::factory()->rejected()->create();

    $this->actingAs($rejected)
        ->get(route('dashboard'))
        ->assertRedirect(route('login'));
    $this->assertGuest();

    $deactivated = User::factory()->inactive()->create();

    $this->actingAs($deactivated)
        ->get(route('dashboard'))
        ->assertRedirect(route('login'));
    $this->assertGuest();
});
