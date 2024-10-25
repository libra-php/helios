<?php

namespace Nebula\Migrations;

use Helios\Database\{Blueprint, Schema, IMigration};

return new class implements IMigration
{
    public function up(): string
    {
        return Schema::create("user_roles", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->varchar("name");
            $table->timestamps();
            $table->primaryKey("id");
        });
    }

    public function afterUp(): string
    {
        return Schema::insert("user_roles",
            ["name"],
            ["Super Admin"],
            ["Admin"],
            ["Standard"],
        );
    }

    public function down(): string
    {
        return Schema::drop("user_roles");
    }
};
