<?php

namespace Nebula\Migrations;

use Helios\Database\{Blueprint, Schema, IMigration};

return new class implements IMigration
{
    public function up(): string
    {
        return Schema::create("follow", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("friend_id");
            $table->timestamps();
            $table->primaryKey("id");
            $table->unique("user_id, friend_id");
            $table->foreignKey("user_id")->references("users", "id")->onDelete("CASCADE");
            $table->foreignKey("friend_id")->references("users", "id")->onDelete("CASCADE");
        });
    }

    public function down(): string
    {
        return Schema::drop("follow");
    }
};
