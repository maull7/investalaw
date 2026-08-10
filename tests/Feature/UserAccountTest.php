<?php

namespace Tests\Feature;

use App\Enums\ReviewStatus;
use App\Models\Regulation;
use App\Models\RegulationCategory;
use App\Models\RegulationType;
use App\Models\ReviewDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_is_logged_in(): void
    {
        $response = $this->post('/register', [
            'name' => 'End User',
            'email' => 'user@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ]);

        $response->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('users', [
            'email' => 'user@example.com',
            'role' => 'user',
        ]);

        $user = User::where('email', 'user@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertNotEquals('secret-password', $user->password);
        $this->assertTrue(password_verify('secret-password', $user->password));
    }

    public function test_register_validates_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->post('/register', [
            'name' => 'End User',
            'email' => 'taken@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_register_validates_password_confirmation(): void
    {
        $response = $this->post('/register', [
            'name' => 'End User',
            'email' => 'user@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_user_document_index_only_shows_own_documents(): void
    {
        $other = User::factory()->create(['role' => 'user']);
        $owner = User::factory()->create(['role' => 'user']);

        ReviewDocument::create(['user_id' => $other->id, 'title' => 'Dokumen Lain', 'file_path' => 'review-documents/fixture.pdf']);
        ReviewDocument::create(['user_id' => $owner->id, 'title' => 'Dokumen Saya', 'file_path' => 'review-documents/fixture.pdf']);

        $response = $this->actingAs($owner)->get(route('review-documents.index'));

        $response->assertOk();
        $response->assertSee('Dokumen Saya');
        $response->assertDontSee('Dokumen Lain');
    }

    public function test_user_cannot_view_other_users_document(): void
    {
        $other = User::factory()->create(['role' => 'user']);
        $owner = User::factory()->create(['role' => 'user']);

        $document = ReviewDocument::create([
            'user_id' => $other->id,
            'title' => 'Dokumen Rahasia',
            'file_path' => 'review-documents/fixture.pdf',
            'status' => ReviewStatus::Submitted->value,
        ]);

        $this->actingAs($owner)->get(route('review-documents.show', $document))->assertForbidden();
        $this->actingAs($owner)->get(route('review-documents.view-file', $document))->assertForbidden();
        $this->actingAs($owner)->get(route('review-documents.viewer', $document))->assertForbidden();
    }

    public function test_user_can_open_own_document_viewer(): void
    {
        $owner = User::factory()->create(['role' => 'user']);

        $document = ReviewDocument::create([
            'user_id' => $owner->id,
            'title' => 'Dokumen Saya',
            'file_path' => 'review-documents/fixture.pdf',
            'status' => ReviewStatus::Draft->value,
        ]);

        $this->actingAs($owner)->get(route('review-documents.viewer', $document))->assertOk();
    }

    public function test_user_cannot_create_regulation(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->post('/regulations', [
            'regulation_number' => 'PP-99',
            'title' => 'Hack Regulasi',
            'regulation_type_id' => 1,
            'year' => 2026,
        ])->assertForbidden();

        $this->assertDatabaseMissing('regulations', ['title' => 'Hack Regulasi']);
    }

    public function test_user_can_read_regulation_list(): void
    {
        Regulation::create([
            'regulation_number' => 'PP-1',
            'title' => 'Regulasi Terbuka',
            'year' => 2026,
            'file_path' => 'regulations/fixture.pdf',
            'regulation_type_id' => RegulationType::create(['name' => 'Peraturan', 'level' => 1])->id,
            'category_id' => RegulationCategory::create(['name' => 'Kategori Umum'])->id,
        ]);

        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->get(route('regulations.index'))->assertOk()
            ->assertSee('Regulasi Terbuka');
    }
}
