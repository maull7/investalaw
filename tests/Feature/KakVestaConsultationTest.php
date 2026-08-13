<?php

namespace Tests\Feature;

use App\Models\ConsultationSession;
use App\Models\Package;
use App\Models\Regulation;
use App\Models\RegulationCategory;
use App\Models\RegulationType;
use App\Models\User;
use App\Models\UserPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class KakVestaConsultationTest extends TestCase
{
    use RefreshDatabase;

    private function paidUser(): User
    {
        $user = User::factory()->create(['role' => 'user']);
        $package = Package::create(['name' => 'Bisnis', 'price' => '12,5jt', 'benefits' => []]);
        UserPackage::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'type' => 'paid',
            'status' => 'active',
        ]);

        return $user;
    }

    private function trialUser(): User
    {
        $user = User::factory()->create(['role' => 'user']);
        $package = Package::create(['name' => 'Free Trial', 'price' => '0', 'benefits' => []]);
        UserPackage::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'type' => 'trial',
            'status' => 'active',
        ]);

        return $user;
    }

    private function makeRegulations(int $count): array
    {
        $type = RegulationType::create(['name' => 'POJK', 'level' => 1]);
        $category = RegulationCategory::create(['name' => 'Kategori']);

        $ids = [];
        for ($i = 1; $i <= $count; $i++) {
            $ids[] = Regulation::create([
                'regulation_number' => "POJK/{$i}/2026",
                'title' => "Regulasi Test {$i}",
                'regulation_type_id' => $type->id,
                'category_id' => $category->id,
                'year' => 2026,
                'file_path' => "regulations/test-{$i}.pdf",
            ])->id;
        }

        return $ids;
    }

    public function test_free_trial_user_can_access_index(): void
    {
        $this->actingAs($this->trialUser())
            ->get(route('consultations.index'))
            ->assertOk();
    }

    public function test_free_trial_user_can_create_session_and_starts_clock(): void
    {
        $ids = $this->makeRegulations(2);

        $this->actingAs($this->trialUser())
            ->post(route('consultations.store'), ['regulation_ids' => $ids])
            ->assertRedirect();

        $session = ConsultationSession::firstOrFail();
        $this->assertSame(2, $session->regulations()->count());

        $userPackage = UserPackage::where('type', 'trial')->firstOrFail();
        $this->assertNotNull($userPackage->kak_vesta_started_at);
    }

    public function test_free_trial_user_reuses_existing_clock(): void
    {
        $trial = $this->trialUser();
        UserPackage::where('user_id', $trial->id)->update(['kak_vesta_started_at' => now()->subHour()]);
        $ids = $this->makeRegulations(2);

        $this->actingAs($trial)
            ->post(route('consultations.store'), ['regulation_ids' => $ids])
            ->assertRedirect();

        $userPackage = UserPackage::where('user_id', $trial->id)->firstOrFail();
        $this->assertNotNull($userPackage->fresh()->kak_vesta_started_at);
    }

    public function test_free_trial_user_blocked_after_duration_elapses(): void
    {
        $trial = $this->trialUser();
        UserPackage::where('user_id', $trial->id)
            ->update(['kak_vesta_started_at' => now()->subHours(48)]);

        $this->actingAs($trial)
            ->get(route('consultations.index'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');
    }

    public function test_free_trial_user_blocked_after_duration_elapses_on_ask(): void
    {
        $trial = $this->trialUser();
        UserPackage::where('user_id', $trial->id)
            ->update(['kak_vesta_started_at' => now()->subHours(49)]);

        $session = ConsultationSession::create(['user_id' => $trial->id, 'title' => 'Sesi']);

        $this->actingAs($trial)
            ->postJson(route('consultations.chat.ask', $session), ['question' => 'halo'])
            ->assertRedirect(route('dashboard'));
    }

    public function test_paid_user_can_create_session(): void
    {
        $ids = $this->makeRegulations(3);

        $this->actingAs($this->paidUser())
            ->post(route('consultations.store'), ['regulation_ids' => $ids])
            ->assertRedirect();

        $session = ConsultationSession::firstOrFail();
        $this->assertSame(3, $session->regulations()->count());
    }

    public function test_session_requires_min_one_regulation(): void
    {
        $this->actingAs($this->paidUser())
            ->post(route('consultations.store'), ['regulation_ids' => []])
            ->assertSessionHasErrors('regulation_ids');
    }

    public function test_admin_can_access_without_paying(): void
    {
        Http::fake([
            '*/chat/completions' => Http::response([
                'choices' => [['message' => ['content' => 'Jawaban.']]],
            ]),
        ]);

        $admin = User::factory()->create(['role' => 'admin']);
        $ids = $this->makeRegulations(2);

        $this->actingAs($admin)->post(route('consultations.store'), ['regulation_ids' => $ids]);
        $session = ConsultationSession::firstOrFail();
        $this->assertSame(2, $session->regulations()->count());
    }

    public function test_add_regulations_respects_max_limit(): void
    {
        $ids = $this->makeRegulations(12);
        $sessionIds = array_slice($ids, 0, 9);

        $user = $this->paidUser();
        $this->actingAs($user)->post(route('consultations.store'), ['regulation_ids' => $sessionIds]);
        $session = ConsultationSession::firstOrFail();
        $this->assertSame(9, $session->regulations()->count());
    }

    public function test_cross_user_isolation(): void
    {
        $userA = $this->paidUser();
        $userB = $this->paidUser();
        $ids = $this->makeRegulations(2);

        $this->actingAs($userA)->post(route('consultations.store'), ['regulation_ids' => $ids]);
        $session = ConsultationSession::firstOrFail();

        $this->actingAs($userB)
            ->get(route('consultations.show', $session))
            ->assertForbidden();

        $this->actingAs($userB)
            ->postJson(route('consultations.chat.ask', $session), ['question' => 'x'])
            ->assertForbidden();
    }

    public function test_store_validates_max_ten(): void
    {
        $ids = $this->makeRegulations(11);

        $this->actingAs($this->paidUser())
            ->post(route('consultations.store'), ['regulation_ids' => $ids])
            ->assertSessionHasErrors('regulation_ids');
    }

    public function test_kak_vesta_usage_helper_reports_elapsed_and_cap(): void
    {
        $trial = $this->trialUser();
        UserPackage::where('user_id', $trial->id)->update([
            'kak_vesta_started_at' => now()->subHours(2)->subMinutes(15),
        ]);
        $trial->load('userPackages.package');

        $usage = $trial->kakVestaUsage();

        $this->assertNotNull($usage);
        $this->assertSame(135, $usage['elapsed_minutes']);
        $this->assertSame(48 * 60, $usage['allowed_minutes']);
        $this->assertFalse($usage['expired']);
    }

    public function test_kak_vesta_usage_helper_marks_expired(): void
    {
        $trial = $this->trialUser();
        UserPackage::where('user_id', $trial->id)->update([
            'kak_vesta_started_at' => now()->subHours(49),
        ]);
        $trial->load('userPackages.package');

        $this->assertTrue($trial->kakVestaUsage()['expired']);
    }

    public function test_admin_users_page_shows_kak_vesta_usage(): void
    {
        $trial = $this->trialUser();
        UserPackage::where('user_id', $trial->id)->update([
            'kak_vesta_started_at' => now()->subHour(),
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('Kak Vesta');
    }
}
