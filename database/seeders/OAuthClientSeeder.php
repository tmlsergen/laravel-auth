<?php

namespace Database\Seeders;

use App\Enums\OAuthGrantType;
use App\Enums\OAuthScope;
use App\Models\OAuthClient;
use Illuminate\Database\Seeder;

class OAuthClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $client = OAuthClient::query()->create([
            'name' => 'Test Client',
            'client_id' => 'test-client-id',
            'client_secret' => base64_encode(random_bytes(32)),
            'redirect_uri' => 'http://test-client.com/callback',
            'grant_types' => OAuthGrantType::cases(),
            'scopes' => OAuthScope::cases(),
        ]);
    }
}
