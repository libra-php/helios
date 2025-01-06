<?php
namespace Nebula\Migrations;

use Helios\Database\{Blueprint, Schema, IMigration};

return new class implements IMigration
{
    private $table = "blog_posts";
    public function up(): string
    {
        return Schema::create($this->table, function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("cover_image")->nullable();
            $table->unsignedBigInteger("category_id")->nullable()->default(1);
            $table->unsignedBigInteger("status_id")->default(2);
            $table->varchar("title");
            $table->varchar("subtitle");
            $table->varchar("slug");
            $table->text("content")->nullable();
            $table->unique("slug");
            $table->timestamp("publish_at")->nullable();
            $table->timestamps();
            $table->primaryKey("id");
            $table->foreignKey("user_id")->references("users", "id");
            $table->foreignKey("category_id")->references("blog_categories", "id")->onDelete("SET NULL");
            $table->foreignKey("status_id")->references("blog_post_status", "id");
            $table->foreignKey("cover_image")->references("files", "id")->onDelete("SET NULL");
        });
    }

    public function down(): string
    {
        return Schema::drop($this->table);
    }
};
