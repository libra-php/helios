<?php

namespace Helios\Database;

interface IMigration
{
    public function up(): string;
    public function down(): string;
}
