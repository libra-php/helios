<?php

namespace Nebula\Migrations;

use Helios\Database\{Blueprint, Schema, IMigration};

return new class implements IMigration
{
    public function up(): string
    {
        return Schema::create("modules", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->tinyInteger("enabled")->default(1);
            $table->varchar("title");
            $table->varchar("path")->nullable();
            $table->varchar("module_class")->nullable();
            $table->tinyInteger("item_order")->default(0);
            $table->tinyInteger("max_permission_level")->nullable();
            $table->unsignedBigInteger("parent_module_id")->nullable();
            $table->timestamps();
            $table->primaryKey("id");
            $table->foreignKey("parent_module_id")->references("modules", "id");
        });
    }

    public function afterUp(): string
    {
        return Schema::insert(
            "modules",
            [
                "title",
                "path",
                "module_class",
                "item_order",
                "max_permission_level",
                "parent_module_id"
            ],
            ["Administration", NULL, NULL, 0, NULL, NULL],
            ["Account", NULL, NULL, 1, NULL, NULL],
            ["Users", "users", "App\\\Modules\\\Users", 0, 1, 1],
            ["User Types", "user-types", "App\\\Modules\\\UserTypes", 0, 0, 3],
            ["Modules", "modules", "App\\\Modules\\\Modules", 1, 0, 1],
            ["Sessions", "sessions", "App\\\Modules\\\Sessions", 2, 1, 1],
            ["Audit", "audit", "App\\\Modules\\\Audit", 3, 0, 1],
            ["Profile", "profile", "App\\\Modules\\\Profile", 0, 2, 2],
            ["Tests", "tests", "App\\\Modules\\\Tests", 1, 0, 1],
        );
    }

    public function down(): string
    {
        return Schema::drop("modules");
    }
};
