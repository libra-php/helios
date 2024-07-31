<?php

namespace Nebula\Migrations;

use Helios\Admin\Auth;
use Helios\Database\{Blueprint, Schema, IMigration};

return new class implements IMigration
{
    public function up(): string
    {
        return Schema::create("users", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->uuid("uuid")->default("(UUID())");
            $table->varchar("name");
            $table->varchar("email");
            $table->binary("password", 96);
            $table->timestamp("login_at")->default("CURRENT_TIMESTAMP");
            $table->char("secret_key", 16)->nullable();
            $table->unsignedTinyInteger("enable_2fa")->default(1);
            $table->timestamps();
            $table->unique("email");
            $table->primaryKey("id");
        });
    }

    public function afterUp(): string
    {
        return Schema::insert(
            "users",
            [
                "name",
                "email",
                "password",
                "secret_key",
                "enable_2fa",
            ],
            [
                "Administrator",
                "administrator@localhost",
                Auth::hashPassword("admin2024!"),
                Auth::generateSecretKey(),
                0,
            ]
        );
    }

    public function down(): string
    {
        return Schema::drop("users");
    }
};
