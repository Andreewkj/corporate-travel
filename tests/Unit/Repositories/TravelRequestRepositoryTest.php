<?php

declare(strict_types=1);

namespace Tests\Unit\Repositories;

use App\Infra\Repositories\TravelRequestRepository;
use App\Models\TravelRequestModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TravelRequestRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_filters_travel_requests(): void
    {
        $user = User::factory()->create();

        TravelRequestModel::create([
            'user_id' => $user->id,
            'destination' => 'Recife',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-05',
            'status' => 'aprovado',
        ]);

        TravelRequestModel::create([
            'user_id' => $user->id,
            'destination' => 'Sao Paulo',
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-05',
            'status' => 'solicitado',
        ]);

        $repository = new TravelRequestRepository();

        $results = $repository->all([
            'status' => 'aprovado',
            'destination' => 'Rec',
            'start_date' => '2026-06-01',
            'end_date' => '2026-06-30',
        ]);

        $this->assertCount(1, $results);
        $this->assertSame('Recife', (string) $results[0]->destination());
        $this->assertSame('aprovado', $results[0]->status()->value);
    }
}
