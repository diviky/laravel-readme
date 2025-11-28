<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE readme_documents ADD FULLTEXT INDEX fulltext_content (content)');
        } elseif ($driver === 'pgsql') {
            DB::statement('CREATE INDEX readme_documents_content_fulltext_idx ON readme_documents USING gin(to_tsvector(\'english\', content))');
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE readme_documents DROP INDEX fulltext_content');
        } elseif ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS readme_documents_content_fulltext_idx');
        }
    }
};

