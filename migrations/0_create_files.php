<?php
namespace Nebula\Migrations;

use Helios\Database\{Blueprint, Schema, IMigration};

return new class implements IMigration
{
    private $table = "files";
    public function up(): string
    {
        return Schema::create($this->table, function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->uuid("uuid");
            $table->varchar("name");
            $table->text("path");
            $table->timestamp("created_at")->default("CURRENT_TIMESTAMP");
            $table->primaryKey("id");
        });
    }

    public function down(): string
    {
        return Schema::drop($this->table);
    }
};
