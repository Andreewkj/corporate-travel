<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Use raw SQL to avoid requiring doctrine/dbal for column modification
        DB::statement('ALTER TABLE travel_requests MODIFY end_date DATE NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE travel_requests MODIFY end_date DATE NULL');
    }
};
