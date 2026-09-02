<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
/**
 * Run the migrations.
 */
public function up(): void
{
Schema::create('user_program_types', function (Blueprint $table) {
$table->id();
$table->foreignId('user_id')
->constrained()
->cascadeOnDelete();

$table->unsignedBigInteger('program_type_id');

$table->timestamps();

$table->unique([
'user_id',
'program_type_id',
]);

$table->index('program_type_id');

});
}

/**
 * Reverse the migrations.
 */
public function down(): void
{
Schema::dropIfExists('user_program_types');
}
};
