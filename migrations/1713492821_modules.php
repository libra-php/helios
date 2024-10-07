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
            ["Administration", NULL, NULL, 1, NULL, NULL],
            ["Uploads", NULL, NULL, 1, 0, NULL],
            ["Account", NULL, NULL, 2, NULL, NULL],
            ["Users", "users", "App\\\Modules\\\Users", 0, 1, 1],
            ["Roles", "user-roles", "App\\\Modules\\\UserRoles", 0, 0, 4],
            ["Modules", "modules", "App\\\Modules\\\Modules", 1, 0, 1],
            ["Sessions", "sessions", "App\\\Modules\\\Sessions", 2, 1, 1],
            ["Audit", "audit", "App\\\Modules\\\Audit", 3, 0, 1],
            ["Files", "files", "App\\\Modules\\\Files", 4, 0, 2],
            ["Home", "home", "App\\\Modules\\\Home", 0, 2, 3],
            ["Notifications", "notifications", "App\\\Modules\\\Notifications", 1, 2, 3],
            ["Profile", "profile", "App\\\Modules\\\Profile", 2, 2, 3],
        );
    }

    public function down(): string
    {
        return Schema::drop("modules");
    }
};
