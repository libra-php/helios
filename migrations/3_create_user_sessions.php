<?php

namespace Nebula\Migrations;

use Helios\Database\{Blueprint, Schema, IMigration};

return new class implements IMigration
{
    private $table = "user_sessions";
    public function up(): string
    {
        return Schema::create($this->table, function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->unsignedBigInteger("user_id");
            $table->varchar("module");
            $table->text("url");
            $table->unsignedInteger("ip");
            $table->timestamp("created_at")->default("CURRENT_TIMESTAMP");
            $table->primaryKey("id");
            $table->foreignKey("user_id")->references("users", "id")->onDelete("CASCADE");
        });
    }

    public function down(): string
    {
        return Schema::drop($this->table);
    }
};
