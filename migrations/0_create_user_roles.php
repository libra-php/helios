<?php

namespace Nebula\Migrations;

use Helios\Database\{Blueprint, Schema, IMigration};

return new class implements IMigration
{
    private $table = "user_roles";
    public function up(): string
    {
        return Schema::create($this->table, function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->varchar("name");
            $table->unique("name");
            $table->timestamps();
            $table->primaryKey("id");
        });
    }

    public function afterUp(): string
    {
        return Schema::insert($this->table,
            ["name"],
            ["Super Admin"],
            ["Admin"],
            ["Standard"],
        );
    }

    public function down(): string
    {
        return Schema::drop($this->table);
    }
};
