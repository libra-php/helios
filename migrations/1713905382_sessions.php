<?php

namespace Nebula\Migrations;

use Helios\Database\{Blueprint, Schema, IMigration};

return new class implements IMigration
{
    public function up(): string
    {
        return Schema::create("sessions", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->mediumText("request_uri")->nullable();
            $table->unsignedInteger("ip")->nullable();
            $table->unsignedBigInteger("user_id")->nullable();
            $table->unsignedBigInteger("module_id")->nullable();
            $table->timestamp("created_at")->default("CURRENT_TIMESTAMP");
            $table->primaryKey("id");
            $table->foreignKey("user_id")->references("users", "id")->onDelete("SET NULL");
            $table->foreignKey("module_id")->references("modules", "id")->onDelete("SET NULL");
        });
    }

    public function down(): string
    {
        return Schema::drop("sessions");
    }
};
