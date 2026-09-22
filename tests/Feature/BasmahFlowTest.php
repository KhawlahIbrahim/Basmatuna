<?php

namespace Tests\Feature;

use App\Enums\ParticipationStatus;
use App\Models\Participation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BasmahFlowTest extends TestCase
{
    use RefreshDatabase;

    private function valid(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'أ. سارة أحمد',
            'department' => config('basmah.departments')[0],
            'selected_word' => 'طموح',
            'message' => 'السعودية بالنسبة لي وطن الطموح والفرص.',
            'consent' => '1',
        ], $overrides);
    }

    public function test_submission_is_saved_as_pending_and_hidden_from_public(): void
    {
        $this->post('/participate', $this->valid())->assertRedirect(route('participate.success'));

        $p = Participation::firstOrFail();
        $this->assertSame(ParticipationStatus::Pending, $p->status);
        $this->get('/explore')->assertOk()->assertDontSee('أ. سارة أحمد');
        $this->getJson('/display/feed')->assertOk()->assertJsonCount(0, 'items');
    }

    public function test_consent_is_required(): void
    {
        $this->post('/participate', $this->valid(['consent' => null]))->assertSessionHasErrors('consent');
        $this->assertDatabaseCount('participations', 0);
    }

    public function test_html_is_stripped_and_escaped(): void
    {
        $this->post('/participate', $this->valid(['message' => '<script>alert(1)</script>وطن العطاء والأمان']));
        $this->assertStringNotContainsString('<script>', Participation::first()->message);
    }

    public function test_approved_participation_appears_publicly(): void
    {
        Participation::create($this->valid() + ['status' => ParticipationStatus::Approved, 'consent' => true]);

        $this->get('/explore')->assertOk()->assertSee('أ. سارة أحمد')->assertSee('ماذا قالت بصمتنا؟');
        $this->getJson('/display/feed')->assertJsonPath('count', 1)->assertJsonPath('items.0.word', 'طموح');
    }

    public function test_admin_area_requires_admin(): void
    {
        $this->get('/admin')->assertRedirect(route('admin.login'));

        $user = User::factory()->create();
        $this->actingAs($user)->get('/admin')->assertForbidden();

        $user->is_admin = true;
        $user->save();
        $this->actingAs($user)->get('/admin')->assertOk();
    }

    public function test_admin_can_approve(): void
    {
        $admin = User::factory()->create();
        $admin->is_admin = true;
        $admin->save();
        $p = Participation::create($this->valid() + ['status' => ParticipationStatus::Pending, 'consent' => true]);

        $this->actingAs($admin)->patch(route('admin.participations.status', $p), ['status' => 'approved'])->assertRedirect();
        $this->assertSame(ParticipationStatus::Approved, $p->fresh()->status);
    }
}
