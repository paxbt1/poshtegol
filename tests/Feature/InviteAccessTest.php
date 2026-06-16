<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\InviteLink;
use App\Models\ReferralRelation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InviteAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_root_shows_public_homepage_by_default(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_auth_page_is_available_for_login_context(): void
    {
        $this->getJson('/auth')->assertOk();
        $this->getJson('/join/invalid-code')->assertOk();
    }

    public function test_auth_without_invite_context_still_shows_login_page(): void
    {
        $this->get('/auth')->assertOk();
    }

    public function test_valid_master_access_link_stores_context_without_incrementing_usage(): void
    {
        $invite = InviteLink::create(['code' => 'MASTER1', 'type' => InviteLink::TYPE_MASTER_ACCESS, 'title' => 'ورود مادر']);

        $this->get(route('join', $invite->code))
            ->assertRedirect(route('login'))
            ->assertSessionHas('invite_link_id', $invite->id)
            ->assertSessionHas('invite_type', InviteLink::TYPE_MASTER_ACCESS);

        $this->assertSame(0, $invite->refresh()->used_count);
    }

    public function test_inactive_expired_or_overused_invite_is_unauthorized(): void
    {
        InviteLink::create(['code' => 'OFF', 'type' => InviteLink::TYPE_MASTER_ACCESS, 'is_active' => false]);
        InviteLink::create(['code' => 'OLD', 'type' => InviteLink::TYPE_MASTER_ACCESS, 'expires_at' => now()->subMinute()]);
        InviteLink::create(['code' => 'FULL', 'type' => InviteLink::TYPE_MASTER_ACCESS, 'max_uses' => 1, 'used_count' => 1]);

        $this->get('/join/OFF')->assertOk();
        $this->get('/join/OLD')->assertOk();
        $this->get('/join/FULL')->assertOk();
    }

    public function test_registration_through_master_access_has_no_referrer(): void
    {
        $invite = InviteLink::create(['code' => 'MASTER2', 'type' => InviteLink::TYPE_MASTER_ACCESS, 'title' => 'ورود مادر']);

        $this->withSession($this->inviteSession($invite))
            ->postJson(route('auth.register'), $this->registrationPayload('09125550000'))
            ->assertOk();

        $user = User::where('mobile', '09125550000')->firstOrFail();
        $this->assertSame('MASTER2', $user->registered_via_invite_code);
        $this->assertSame(InviteLink::TYPE_MASTER_ACCESS, $user->registered_via_invite_type);
        $this->assertNull($user->direct_referrer_user_id);
        $this->assertNull($user->invited_by_user_id);
        $this->assertSame(0, ReferralRelation::count());
        $this->assertSame(1, $invite->refresh()->used_count);
    }

    public function test_user_referral_link_creates_direct_referral(): void
    {
        $owner = User::factory()->create();
        $invite = InviteLink::create([
            'code' => 'REFUSER1',
            'owner_user_id' => $owner->id,
            'type' => InviteLink::TYPE_USER_REFERRAL,
            'title' => 'دعوت کاربر',
            'earns_commission' => true,
        ]);

        $this->get(route('join', $invite->code))
            ->assertRedirect(route('login'))
            ->assertSessionHas('invite_owner_user_id', $owner->id)
            ->assertSessionHas('invite_earns_commission', true);

        $this->withSession($this->inviteSession($invite))
            ->postJson(route('auth.register'), $this->registrationPayload('09125550001'))
            ->assertOk();

        $user = User::where('mobile', '09125550001')->firstOrFail();
        $this->assertSame($owner->id, $user->direct_referrer_user_id);
        $this->assertSame($owner->id, $user->invited_by_user_id);
        $this->assertTrue(ReferralRelation::where('inviter_user_id', $owner->id)->where('referred_user_id', $user->id)->exists());
        $this->assertSame(1, $invite->refresh()->used_count);
    }

    public function test_authenticated_user_can_open_dashboard_without_invite_context(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_logout_redirects_to_configured_unauthorized_destination(): void
    {
        AppSetting::setValue('unauthorized_access_mode', 'redirect');
        AppSetting::setValue('unauthorized_redirect_url', 'https://www.varzesh3.com');

        $this->actingAs(User::factory()->create())
            ->post(route('logout'))
            ->assertRedirect('https://www.varzesh3.com');
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

    private function registrationPayload(string $mobile): array
    {
        return [
            'first_name' => 'کاربر',
            'last_name' => 'تست',
            'mobile' => $mobile,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
    }
}
