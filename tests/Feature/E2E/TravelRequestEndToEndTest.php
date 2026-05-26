<?php

declare(strict_types=1);

namespace Tests\Feature\E2E;

use App\Infra\Messaging\MessageBusPublisher;
use App\Models\TravelRequestModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\FakeMessageBusPublisher;
use Tests\TestCase;

final class TravelRequestEndToEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_creates_travel_request_and_admin_approves_it(): void
    {
        $fakePublisher = new FakeMessageBusPublisher();
        $this->app->instance(MessageBusPublisher::class, $fakePublisher);

        /** @var User $requester */
        $requester = User::factory()->create([
            'name' => 'Ana Silva',
            'is_admin' => false,
        ]);

        /** @var User $admin */
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $createResponse = $this->actingAs($requester)->postJson('/api/travel-requests', [
            'destination' => 'Recife',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-05',
        ]);

        $createResponse->assertCreated();

        $travelRequest = TravelRequestModel::query()
            ->where('user_id', $requester->id)
            ->firstOrFail();

        $approveResponse = $this->actingAs($admin)->putJson("/api/travel-requests/{$travelRequest->id}/status", [
            'status' => 'aprovado',
        ]);

        $approveResponse->assertOk()
            ->assertJsonPath('data.status', 'aprovado');

        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'user_id' => $requester->id,
            'status' => 'aprovado',
        ]);

        $this->assertCount(1, $fakePublisher->publishedPayloads);
        $this->assertSame('aprovado', $fakePublisher->publishedPayloads[0]['status']);
    }
}
