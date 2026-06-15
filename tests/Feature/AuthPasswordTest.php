<?php

namespace Tests\Feature;

use App\Models\InviteLink;
use App\Models\User;
use App\Services\CardNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_mobile_and_password_works(): void
    {
        $invite = InviteLink::create(['code' => 'LOGIN1', 'type' => InviteLink::TYPE_MASTER_ACCESS]);
        $user = User::factory()->create(['mobile' => '09120000000', 'password' => 'password123']);

        $this->withSession($this->inviteSession($invite))
            ->postJson(route('auth.login'), [
                'mobile' => '۰۹۱۲۰۰۰۰۰۰۰',
                'password' => 'password123',
            ])
            ->assertOk()
            ->assertJsonPath('redirect', route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $invite = InviteLink::create(['code' => 'LOGIN2', 'type' => InviteLink::TYPE_MASTER_ACCESS]);
        User::factory()->create(['mobile' => '09120000001', 'password' => 'password123', 'is_active' => false]);

        $this->withSession($this->inviteSession($invite))
            ->postJson(route('auth.login'), [
                'mobile' => '09120000001',
                'password' => 'password123',
            ])
            ->assertUnprocessable();
    }

    public function test_duplicate_mobile_and_duplicate_card_are_rejected(): void
    {
        $invite = InviteLink::create(['code' => 'REG1', 'type' => InviteLink::TYPE_MASTER_ACCESS]);
        $card = '4242424242424242';
        $service = app(CardNumberService::class);

        User::factory()->create([
            'mobile' => '09121111111',
            'card_number' => $card,
            'card_hash' => $service->hash($card),
        ]);

        $this->withSession($this->inviteSession($invite))
            ->postJson(route('auth.register'), [
                'first_name' => 'کاربر',
                'last_name' => 'تکراری',
                'mobile' => '09121111111',
                'card_number' => '5555555555554444',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertJsonValidationErrors('mobile');

        $this->withSession($this->inviteSession($invite))
            ->postJson(route('auth.register'), [
                'first_name' => 'کاربر',
                'last_name' => 'تکراری',
                'mobile' => '09121111112',
                'card_number' => $card,
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertJsonValidationErrors('card_number');
    }

    private function inviteSession(InviteLink $invite): array
    {
        return [
            'invite_link_id' => $invite->id,
            'invite_code' => $invite->code,
            'invite_type' => $invite->type,
            'invite_owner_user_id' => $invite->owner_user_id,
            'invite_earns_commission' => $invite->earns_commission,
        ];
    }
}
