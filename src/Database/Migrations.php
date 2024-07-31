<?php

namespace Helios\Database;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class Migrations
{
    public function __construct()
    {
        $exists = $this->migrationTableExists();
        if (!$exists) {
            $this->createMigrationTable();
        }
    }

    private function createMigrationTable(): void
    {
        db()->query("CREATE TABLE migrations (
            hash CHAR(32) NOT NULL,
            path MEDIUMTEXT NOT NULL,
            status ENUM('pending', 'complete', 'failure'),
            updated_at TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (hash)
        )");
    }

    public function migrationTableExists(): bool
    {
        $result = db()->fetch("SHOW TABLES LIKE 'migrations'");
        return $result ? true : false;
    }

    public function getMigrationStatus(string $hash): string
    {
        return db()->var("SELECT status FROM migrations WHERE hash = ?", $hash);
    }

    public function setMigrationStatus(string $hash, string $status): void
    {
        $statuses = ["pending", "complete", "failure"];
        if (in_array($status, $statuses)) {
            db()->var(
                "UPDATE migrations SET status = ? WHERE hash = ?",
                $status,
                $hash
            );
        }
    }

    public function getMigrationFiles(string $migration_path): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($migration_path),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $files = [];

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === "php") {
                $pathname = $file->getPathname();
                $md5 = md5($pathname);
                $exists = db()->fetch(
                    "SELECT * FROM migrations WHERE hash = ?",
                    $md5
                );
                if (!$exists) {
                    db()->query(
                        "INSERT INTO migrations SET path = ?, hash = ?, status = 'pending'",
                        $pathname,
                        $md5
                    );
                }
                $files[] = $pathname;
            }
        }

        return $files;
    }

    public function getMigrations(): array
    {
        $migration_path = config("paths.migrations");
        $migrations = $this->getMigrationFiles($migration_path);
        return $migrations;
    }

    public function mapMigrations(): array
    {
        $migs = $this->getMigrations();
        return array_map(
            fn($path) => [
                "path" => $path,
                "name" => basename($path),
                "hash" => md5($path),
                "status" => $this->getMigrationStatus(md5($path)),
                "class" => require $path,
            ],
            $migs
        );
    }

    public function refreshDatabase(): void
    {
        $db_name = config("database.dbname");
        db()->query(sprintf("DROP DATABASE %s", $db_name));
        db()->query(sprintf("CREATE DATABASE %s", $db_name));
        db()->query(sprintf("USE %s", $db_name));
        $exists = $this->migrationTableExists();
        if (!$exists) {
            $this->createMigrationTable();
        }
    }

    public function up(IMigration $migration, string $hash)
    {
        $status = $this->getMigrationStatus($hash);
        if ($status !== "complete") {
            $beforeUp = $afterUp = true;
            if (method_exists($migration, "beforeUp")) {
                $beforeUp = db()->query($migration->beforeUp());
            }
            $up = db()->query($migration->up());
            if (method_exists($migration, "afterUp")) {
                $afterUp = db()->query($migration->afterUp());
            }
            $result = $beforeUp && $up && $afterUp;
            if ($result) {
                $this->setMigrationStatus($hash, "complete");
            } else {
                $this->setMigrationStatus($hash, "failure");
            }
            return $result;
        }
        return null;
    }

    public function down(IMigration $migration, string $hash)
    {
        $status = $this->getMigrationStatus($hash);
        if ($status === "complete") {
            $result = db()->query($migration->down());
            if ($result) {
                $this->setMigrationStatus($hash, "pending");
            } else {
                $this->setMigrationStatus($hash, "failure");
            }
            return $result;
        }
        return null;
    }
}
