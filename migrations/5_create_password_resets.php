<?php

namespace Nebula\Migrations;

use Helios\Database\{Blueprint, Schema, IMigration};

return new class implements IMigration
{
    private $table = "password_resets";
    public function up(): string
    {
        return Schema::create($this->table, function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("email_job_id")->nullable();
            $table->char("token", 64);
            $table->unsignedInteger("ip");
            $table->unsignedTinyInteger("complete")->default(0);
            $table->timestamp("expires_at");
            $table->timestamp("created_at")->default("CURRENT_TIMESTAMP");
            $table->primaryKey("id");
            $table->foreignKey("user_id")->references("users", "id")->onDelete("CASCADE");
            $table->foreignKey("email_job_id")->references("email_jobs", "id")->onDelete("SET NULL");
        });
    }

    public function down(): string
    {
        return Schema::drop($this->table);
    }
};
