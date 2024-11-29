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
            $table->varchar("username");
            $table->varchar("name");
            $table->varchar("email");
            $table->binary("password", 96);
            $table->timestamp("login_at")->nullable();
            $table->unsignedInteger("login_ip")->nullable();
            $table->unsignedTinyInteger("failed_login")->default(0);
            $table->timestamp("locked_until")->nullable();
            $table->unsignedTinyInteger("two_fa_confirmed")->default(0);
            $table->char("two_fa_secret", 16)->nullable();
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
                "two_fa_secret",
            ],
            [
                "Administrator",
                "administrator",
                "admin",
                Auth::hashPassword(config("security.default_admin_pass")),
                Auth::generateTwoFactorSecret(),
            ]
        );
    }

    public function down(): string
    {
        return Schema::drop("users");
    }
};
