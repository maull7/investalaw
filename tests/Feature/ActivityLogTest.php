<?php

namespace Tests\Feature;

use App\Models\Regulation;
use App\Models\RegulationCategory;
use App\Models\RegulationType;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_and_subadmin_can_view_activity_logs_but_user_cannot(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $subAdmin = User::factory()->create(['role' => 'sub_admin']);
        $user = User::factory()->create(['role' => 'user']);

        UserActivityLog::create(['user_id' => $admin->id, 'action' => 'created', 'description' => 'Aktivitas admin']);
        UserActivityLog::create(['user_id' => $subAdmin->id, 'action' => 'updated', 'description' => 'Aktivitas subadmin']);
        UserActivityLog::create(['user_id' => $user->id, 'action' => 'viewed', 'description' => 'Aktivitas user']);

        $this->actingAs($admin)
            ->get(route('activity-logs.index'))
            ->assertOk()
            ->assertSee('Aktivitas admin')
            ->assertSee('Aktivitas subadmin')
            ->assertDontSee('Aktivitas user');

        $this->actingAs($subAdmin)->get(route('activity-logs.index'))->assertOk();

        $this->actingAs($admin)
            ->get(route('activity-logs.index', ['role' => 'sub_admin', 'action' => 'updated']))
            ->assertOk()
            ->assertSee('Aktivitas subadmin')
            ->assertDontSee('Aktivitas admin');

        $this->actingAs($user)->get(route('activity-logs.index'))->assertForbidden();
    }

    public function test_legacy_regulations_without_creator_can_be_deleted_by_any_uploader(): void
    {
        $subAdmin = User::factory()->create([
            'role' => 'sub_admin',
            'permissions' => ['upload_regulations'],
        ]);
        $otherSubAdmin = User::factory()->create([
            'role' => 'sub_admin',
            'permissions' => ['upload_regulations'],
        ]);
        $admin = User::factory()->create(['role' => 'admin']);
        $owned = $this->createRegulation($subAdmin);
        $notOwned = $this->createRegulation($otherSubAdmin);
        $legacy = $this->createRegulation($otherSubAdmin);
        $legacy->update(['created_by' => null]);

        $this->actingAs($subAdmin)
            ->delete(route('regulations.destroy', $owned))
            ->assertRedirect(route('regulations.index'));
        $this->assertSoftDeleted($owned);

        $this->actingAs($subAdmin)
            ->delete(route('regulations.destroy', $notOwned))
            ->assertForbidden();
        $this->assertModelExists($notOwned);

        $this->actingAs($admin)
            ->delete(route('regulations.destroy', $notOwned))
            ->assertRedirect(route('regulations.index'));
        $this->assertSoftDeleted($notOwned);

        $this->actingAs($subAdmin)
            ->delete(route('regulations.destroy', $legacy))
            ->assertRedirect(route('regulations.index'));
        $this->assertSoftDeleted($legacy);
    }

    public function test_regulation_edit_uses_application_file_preview_endpoint(): void
    {
        $subAdmin = User::factory()->create([
            'role' => 'sub_admin',
            'permissions' => ['upload_regulations'],
        ]);
        $regulation = $this->createRegulation($subAdmin);

        $this->actingAs($subAdmin)
            ->get(route('regulations.edit', $regulation))
            ->assertOk()
            ->assertSee(route('regulations.file-raw', $regulation), false)
            ->assertDontSee('/storage/'.$regulation->file_path, false);
    }

    private function createRegulation(User $creator): Regulation
    {
        $category = RegulationCategory::firstOrCreate(['name' => 'Test Category']);
        $type = RegulationType::firstOrCreate(['name' => 'Test Type'], ['level' => 1]);

        return Regulation::create([
            'created_by' => $creator->id,
            'regulation_number' => 'TEST-'.$creator->id.'-'.fake()->unique()->numberBetween(1, 99999),
            'title' => 'Test Regulation',
            'regulation_type_id' => $type->id,
            'category_id' => $category->id,
            'year' => 2026,
            'file_path' => 'regulations/test.pdf',
        ]);
    }
}
