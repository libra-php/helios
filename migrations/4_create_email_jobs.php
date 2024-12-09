<?php

namespace Nebula\Migrations;

use Helios\Database\{Blueprint, Schema, IMigration};

return new class implements IMigration
{
    private $table = "email_jobs";
    public function up(): string
    {
        return Schema::create($this->table, function (Blueprint $table) {
            $table->bigIncrements("id");
            $table->varchar("tag");
            $table->varchar("subject");
            $table->text("body");
            $table->varchar("to_address");
            $table->varchar("cc_address")->nullable();
            $table->varchar("bcc_address")->nullable();
            $table->timestamp("send_at");
            $table->timestamp("created_at")->default("CURRENT_TIMESTAMP");
            $table->unsignedTinyInteger("sent")->default(0);
            $table->unsignedTinyInteger("retries")->default(0);
            $table->primaryKey("id");
        });
    }

    public function down(): string
    {
        return Schema::drop($this->table);
    }
};

