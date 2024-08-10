<?php

namespace Nebula\Migrations;

use Helios\Database\{Blueprint, Schema, IMigration};

return new class implements IMigration
{
    public function up(): string
    {
        return Schema::create("user_types", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->varchar("name");
            $table->tinyInteger("permission_level")->default(2); // default standard user
            $table->timestamps();
            $table->primaryKey("id");
        });
    }

    public function afterUp(): string
    {
        return Schema::insert("user_types",
            ["name", "permission_level"],
            ["Super Admin", 0],
            ["Admin", 1],
            ["Standard", 2],
        );
    }

    public function down(): string
    {
        return Schema::drop("user_types");
    }
};
