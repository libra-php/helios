<?php

namespace Nebula\Migrations;

use Helios\Database\{Blueprint, Schema, IMigration};

return new class implements IMigration
{
    public function up(): string
    {
        return Schema::create("likes", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("post_id");
            $table->timestamps();
            $table->primaryKey("id");
            $table->unique("user_id, post_id");
            $table->foreignKey("user_id")->references("users", "id")->onDelete("CASCADE");
            $table->foreignKey("post_id")->references("posts", "id")->onDelete("CASCADE");
        });
    }

    public function down(): string
    {
        return Schema::drop("likes");
    }
};
