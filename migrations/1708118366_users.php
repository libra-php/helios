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
            $table->unsignedTinyInteger("2fa_enabled")->default(1);
            $table->unsignedTinyInteger("2fa_confirmed")->default(0);
            $table->char("2fa_secret", 16)->nullable();
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
                "2fa_secret",
                "2fa_enabled",
            ],
            [
                "Administrator",
                "administrator@localhost",
                Auth::hashPassword("admin2024!"),
                Auth::google2FASecret(),
                0,
            ]
        );
    }

    public function down(): string
    {
        return Schema::drop("users");
    }
};
