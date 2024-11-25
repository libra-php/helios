<?php

namespace Nebula\Migrations;

use Helios\Database\{Blueprint, Schema, IMigration};

return new class implements IMigration
{
    public function up(): string
    {
        return Schema::create("users", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->uuid("uuid")->default("(UUID())");
            $table->varchar("username");
            $table->varchar("name");
            $table->varchar("email");
            $table->binary("password", 96);
            $table->timestamp("login_at")->nullable();
            $table->unsignedTinyInteger("failed_login")->default(0);
            $table->unsignedTinyInteger("2fa_enabled")->default(1);
            $table->unsignedTinyInteger("2fa_confirmed")->default(0);
            $table->binary("2fa_secret", 96)->nullable();
            $table->timestamps();
            $table->unique("username");
            $table->unique("email");
            $table->primaryKey("id");
        });
    }

    public function afterUp(): string
    {
        return Schema::insert("users",
            [
                "name",
                "email",
                "username",
                "password",
                "2fa_secret",
                "2fa_enabled",
            ],
            [
                "Administrator",
                "administrator",
                "admin",
                password_hash(config("security.default_admin_pass"), PASSWORD_ARGON2I),
                '',
                0,
            ]
        );
    }

    public function down(): string
    {
        return Schema::drop("users");
    }
};
