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
            $table->unsignedBigInteger("user_role_id")->nullable();
            $table->unsignedBigInteger("parent_module_id")->nullable();
            $table->timestamps();
            $table->primaryKey("id");
            $table->foreignKey("user_role_id")->references("user_roles", "id");
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
                "user_role_id",
                "parent_module_id"
            ],
            ["Administration", NULL, NULL, 3, 3, NULL],
            ["Uploads", NULL, NULL, 4, 3, NULL],
            ["Account", NULL, NULL, 2, 3, NULL],
            ["Home", NULL, NULL, 1, 3, NULL],
            ["Users", "users", "App\\\Modules\\\Users", 0, 2, 1],
            ["Roles", "user-roles", "App\\\Modules\\\UserRoles", 0, 1, 5],
            ["Modules", "modules", "App\\\Modules\\\Modules", 1, 1, 1],
            ["Sessions", "sessions", "App\\\Modules\\\Sessions", 2, 2, 1],
            ["Audit", "audit", "App\\\Modules\\\Audit", 3, 1, 1],
            ["Files", "files", "App\\\Modules\\\Files", 4, 1, 2],
            ["Feed", "feed", "App\\\Modules\\\Feed", 0, 3, 4],
            ["Profile", "profile", "App\\\Modules\\\Profile", 0, 3, 3],
        );
    }

    public function down(): string
    {
        return Schema::drop("modules");
    }
};
