<?php
// database/migrations/xxxx_create_feedbacks_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->enum('type', ['bug', 'feature', 'other'])->default('bug');
            $table->enum('status', ['new', 'in_progress', 'resolved'])->default('new');
            $table->string('page')->nullable(); // Page où le feedback a été soumis
            $table->string('email')->nullable(); // Email si utilisateur non connecté
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes(); // Optionnel: pour archiver plutôt que supprimer
        });
    }

    public function down()
    {
        Schema::dropIfExists('feedbacks');
    }
};
