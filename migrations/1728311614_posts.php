<?php

namespace Nebula\Migrations;

use Helios\Database\{Blueprint, Schema, IMigration};

return new class implements IMigration
{
    public function up(): string
    {
        return Schema::create("posts", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->unsignedBigInteger("user_id");
            $table->text("body")->nullable();
            $table->unsignedBigInteger("image_id")->nullable();
            $table->text("external_url")->nullable();
            $table->unsignedBigInteger("parent_id")->nullable();
            $table->timestamps();
            $table->primaryKey("id");
            $table->foreignKey("user_id")->references("users", "id")->onDelete("CASCADE");
            $table->foreignKey("image_id")->references("files", "id")->onDelete("SET NULL");
            $table->foreignKey("parent_id")->references("posts", "id")->onDelete("SET NULL");
        });
    }

    public function down(): string
    {
        return Schema::drop("posts");
    }
};
