<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE learning_experience_attempts MODIFY score DECIMAL(10,2) NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE learning_experience_attempts MODIFY total DECIMAL(10,2) NOT NULL DEFAULT 0');

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE learning_experience_attempts ALTER COLUMN score TYPE DECIMAL(10,2) USING score::numeric');
            DB::statement('ALTER TABLE learning_experience_attempts ALTER COLUMN total TYPE DECIMAL(10,2) USING total::numeric');

            return;
        }

        // sqlite / others: keep integers; fractional score remains in result_json
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE learning_experience_attempts MODIFY score INT UNSIGNED NOT NULL DEFAULT 0');
            DB::statement('ALTER TABLE learning_experience_attempts MODIFY total INT UNSIGNED NOT NULL DEFAULT 0');

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE learning_experience_attempts ALTER COLUMN score TYPE INTEGER USING ROUND(score)::integer');
            DB::statement('ALTER TABLE learning_experience_attempts ALTER COLUMN total TYPE INTEGER USING ROUND(total)::integer');
        }
    }
};
