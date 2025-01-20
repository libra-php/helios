<?php
namespace Nebula\Migrations;

use Helios\Database\{Blueprint, Schema, IMigration};

return new class implements IMigration
{
    private $table = "blog_post_images";
    public function up(): string
    {
        return Schema::create($this->table, function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->unsignedBigInteger("blog_post_id");
            $table->unsignedBigInteger("image");
            $table->varchar("caption")->nullable();
            $table->timestamps();
            $table->primaryKey("id");
            $table->foreignKey("image")->references("files", "id")->onDelete("CASCADE");
            $table->foreignKey("blog_post_id")->references("blog_posts", "id")->onDelete("CASCADE");
        });
    }

    public function down(): string
    {
        return Schema::drop($this->table);
    }
};
