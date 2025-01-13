<?php
namespace Nebula\Migrations;

use Helios\Database\{Blueprint, Schema, IMigration};

return new class implements IMigration
{
    private $table = "blog_post_comments";
    public function up(): string
    {
        return Schema::create($this->table, function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->varchar("name");
            $table->text("comment");
            $table->unsignedInteger("ip");
            $table->unsignedTinyInteger("approved")->default(0);
            $table->timestamps();
            $table->primaryKey("id");
        });
    }

    public function down(): string
    {
        return Schema::drop($this->table);
    }
};
