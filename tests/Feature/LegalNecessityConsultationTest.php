<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalNecessityConsultationTest extends TestCase
{
    use RefreshDatabase;

    public function test_consultation_form_is_publicly_accessible(): void
    {
        $this->get(route('legal-necessities.create'))
            ->assertOk()
            ->assertSee('Konsultasi Hukum')
            ->assertSee('Informasi Permasalahan');
    }

    public function test_submitting_consultation_form_stores_request_and_thanks_user(): void
    {
        $this->post(route('legal-necessities.store'), [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'phone' => '08123456789',
            'message' => 'Saya mengalami sengketa reksa dana.',
        ])
            ->assertRedirect(route('legal-necessities.create'))
            ->assertSessionHas('success', 'Terima kasih. Kami akan menghubungi Anda kembali.');

        $this->assertDatabaseHas('legal_necessities', [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'phone' => '08123456789',
            'message' => 'Saya mengalami sengketa reksa dana.',
        ]);
    }

    public function test_consultation_form_requires_contact_details(): void
    {
        $this->post(route('legal-necessities.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'phone']);
    }

    public function test_json_consultation_submission_still_returns_json(): void
    {
        $this->postJson(route('legal-necessities.store'), [
            'name' => 'Budi',
            'email' => 'budi@example.com',
            'phone' => '08123456789',
            'message' => 'Saya mengalami sengketa reksa dana.',
        ])
            ->assertOk()
            ->assertJson(['message' => 'Kebutuhan hukum berhasil disimpan.']);
    }
}
