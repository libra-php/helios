<?php

namespace Nebula\Migrations;

use Helios\Database\{Blueprint, Schema, IMigration};

return new class implements IMigration
{
    public function up(): string
    {
        return Schema::create("files", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->varchar("filename");
            $table->varchar("original_name");
            $table->varchar("mime_type");
            $table->unsignedBigInteger("size");
            $table->unique("filename");
            $table->timestamps();
            $table->primaryKey("id");
        });
    }

    public function down(): string
    {
        return Schema::drop("files");
    }
};
