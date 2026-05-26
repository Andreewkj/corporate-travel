<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Infra\Messaging\MessageBusPublisher;
use App\Models\TravelRequestModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Fakes\FakeMessageBusPublisher;
use Tests\TestCase;

final class TravelRequestApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_travel_request(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['name' => 'Ana Silva']);

        $response = $this->actingAs($user)->postJson('/api/travel-requests', [
            'destination' => 'Recife',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-05',
        ]);

        $response->assertCreated()
            ->assertJson([
                'message' => 'Travel request created',
            ]);

        $this->assertDatabaseHas('travel_requests', [
            'destination' => 'Recife',
            'status' => 'solicitado',
            'user_id' => $user->id,
        ]);
    }

    public function test_unauthenticated_user_cannot_create_travel_request(): void
    {
        $response = $this->postJson('/api/travel-requests', [
            'destination' => 'Recife',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-05',
        ]);

        $response->assertUnauthorized();
    }

    public function test_it_lists_travel_requests_using_filters(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['name' => 'Ana Silva']);
        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();

        TravelRequestModel::create([
            'user_id' => $user->id,
            'destination' => 'Recife',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-05',
            'status' => 'aprovado',
        ]);

        TravelRequestModel::create([
            'user_id' => $anotherUser->id,
            'destination' => 'Sao Paulo',
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-05',
            'status' => 'cancelado',
        ]);

        $response = $this->actingAs($user)->getJson('/api/travel-requests?status=aprovado&destination=Recife');

        $response->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'destination' => 'Recife',
                'status' => 'aprovado',
                'user_name' => 'Ana Silva',
            ])
            ->assertJsonMissing([
                'user_id' => $user->id,
            ]);
    }

    public function test_it_rejects_invalid_filters(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['name' => 'Ana Silva']);

        $response = $this->actingAs($user)->getJson('/api/travel-requests?status=invalido');

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Invalid filters',
            ]);
    }

    public function test_user_can_show_only_own_travel_request(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['name' => 'Ana Silva']);
        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();

        $ownTravelRequest = TravelRequestModel::create([
            'user_id' => $user->id,
            'destination' => 'Recife',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-05',
            'status' => 'solicitado',
        ]);

        $anotherTravelRequest = TravelRequestModel::create([
            'user_id' => $anotherUser->id,
            'destination' => 'Sao Paulo',
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-05',
            'status' => 'solicitado',
        ]);

        $this->actingAs($user)
            ->getJson("/api/travel-requests/{$ownTravelRequest->id}")
            ->assertOk()
            ->assertJsonFragment([
                'id' => $ownTravelRequest->id,
                'user_name' => 'Ana Silva',
            ])
            ->assertJsonMissing([
                'user_id' => $user->id,
            ]);

        $this->actingAs($user)
            ->getJson("/api/travel-requests/{$anotherTravelRequest->id}")
            ->assertNotFound();
    }

    public function test_admin_can_update_status(): void
    {
        $fakePublisher = new FakeMessageBusPublisher();
        $this->app->instance(MessageBusPublisher::class, $fakePublisher);

        /** @var User $requester */
        $requester = User::factory()->create(['name' => 'Ana Silva']);
        /** @var User $admin */
        $admin = User::factory()->create(['is_admin' => true]);

        $travelRequest = TravelRequestModel::create([
            'user_id' => $requester->id,
            'destination' => 'Recife',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-05',
            'status' => 'solicitado',
        ]);

        $response = $this->actingAs($admin)->putJson("/api/travel-requests/{$travelRequest->id}/status", [
            'status' => 'aprovado',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'aprovado')
            ->assertJsonPath('data.user_name', 'Ana Silva')
            ->assertJsonMissing([
                'user_id' => $requester->id,
            ]);

        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'status' => 'aprovado',
        ]);

        $this->assertCount(1, $fakePublisher->publishedPayloads);
    }

    public function test_non_admin_cannot_update_status(): void
    {
        /** @var User $requester */
        $requester = User::factory()->create();
        /** @var User $user */
        $user = User::factory()->create(['is_admin' => false]);

        $travelRequest = TravelRequestModel::create([
            'user_id' => $requester->id,
            'destination' => 'Recife',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-05',
            'status' => 'solicitado',
        ]);

        $response = $this->actingAs($user)->putJson("/api/travel-requests/{$travelRequest->id}/status", [
            'status' => 'aprovado',
        ]);

        $response->assertForbidden()
            ->assertJson([
                'message' => 'Only administrators can update travel request status',
            ]);
    }

    public function test_admin_cannot_approve_canceled_travel_request(): void
    {
        /** @var User $requester */
        $requester = User::factory()->create();
        /** @var User $admin */
        $admin = User::factory()->create(['is_admin' => true]);

        $travelRequest = TravelRequestModel::create([
            'user_id' => $requester->id,
            'destination' => 'Recife',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-05',
            'status' => 'cancelado',
        ]);

        $response = $this->actingAs($admin)->putJson("/api/travel-requests/{$travelRequest->id}/status", [
            'status' => 'aprovado',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Cannot approve a canceled travel request',
            ]);

        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'status' => 'cancelado',
        ]);
    }

    public function test_admin_cannot_update_travel_request_to_same_status(): void
    {
        /** @var User $requester */
        $requester = User::factory()->create();
        /** @var User $admin */
        $admin = User::factory()->create(['is_admin' => true]);

        $travelRequest = TravelRequestModel::create([
            'user_id' => $requester->id,
            'destination' => 'Recife',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-05',
            'status' => 'aprovado',
        ]);

        $response = $this->actingAs($admin)->putJson("/api/travel-requests/{$travelRequest->id}/status", [
            'status' => 'aprovado',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Travel request already has this status',
            ]);

        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'status' => 'aprovado',
        ]);
    }

    public function test_admin_can_update_own_status(): void
    {
        /** @var User $requester */
        $requester = User::factory()->create([
            'name' => 'Ana Silva',
            'is_admin' => true,
        ]);

        $travelRequest = TravelRequestModel::create([
            'user_id' => $requester->id,
            'destination' => 'Recife',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-05',
            'status' => 'solicitado',
        ]);

        $response = $this->actingAs($requester)->putJson("/api/travel-requests/{$travelRequest->id}/status", [
            'status' => 'aprovado',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.status', 'aprovado')
            ->assertJsonPath('data.user_name', 'Ana Silva')
            ->assertJsonMissing([
                'user_id' => $requester->id,
            ]);

        $this->assertDatabaseHas('travel_requests', [
            'id' => $travelRequest->id,
            'user_id' => $requester->id,
            'status' => 'aprovado',
        ]);
    }
}
