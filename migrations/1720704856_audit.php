<?php

namespace Nebula\Migrations;

use Helios\Database\{Blueprint, Schema, IMigration};

return new class implements IMigration
{
    public function up(): string
    {
        return Schema::create("audit", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->unsignedBigInteger("user_id")->nullable();
            $table->varchar("table_name");
            $table->varchar("table_id");
            $table->varchar("field");
            $table->text("old_value")->nullable();
            $table->text("new_value")->nullable();
            $table->varchar("tag")->nullable();
            $table->timestamp("created_at")->default("CURRENT_TIMESTAMP");
            $table->primaryKey("id");
            $table->foreignKey("user_id")->references("users", "id")->onDelete("SET NULL");
        });
    }

    public function down(): string
    {
        return Schema::drop("user_types");
    }
};
