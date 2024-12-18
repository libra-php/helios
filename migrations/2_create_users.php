<?php

namespace Nebula\Migrations;

use Helios\Admin\Auth;
use Helios\Database\{Blueprint, Schema, IMigration};

return new class implements IMigration
{
    private $table = "users";
    public function up(): string
    {
        return Schema::create($this->table, function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->unsignedBigInteger("user_role_id")->default(3);
            $table->unsignedBigInteger("avatar")->nullable();
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
            $table->foreignKey("user_role_id")->references("user_roles", "id");
            $table->foreignKey("avatar")->references("files", "id")->onDelete("SET NULL");
        });
    }

    public function afterUp(): string
    {
        return Schema::insert($this->table,
            [
                "name",
                "email",
                "username",
                "password",
                "two_fa_secret",
                "user_role_id",
            ],
            [
                "Administrator",
                "administrator",
                "admin",
                Auth::hashPassword(config("security.default_admin_pass")),
                Auth::generateTwoFactorSecret(),
                1
            ]
        );
    }

    public function down(): string
    {
        return Schema::drop($this->table);
    }
};
