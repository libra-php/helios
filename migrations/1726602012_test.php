<?php

namespace Nebula\Migrations;

use Helios\Database\{Blueprint, Schema, IMigration};

return new class implements IMigration
{
    public function up(): string
    {
        return Schema::create("test", function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->varchar("control_input")->nullable();
            $table->unsignedBigInteger("control_select")->nullable();
            $table->tinyInteger("control_switch")->nullable();
            $table->text("control_textarea")->nullable();
            $table->timestamps();
            $table->primaryKey("id");
        });
    }

    public function down(): string
    {
        return Schema::drop("user_types");
    }
};
