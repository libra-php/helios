<?php

namespace Helios\Kernel;

use splitbrain\phpcli\CLI;
use splitbrain\phpcli\Options;
use Helios\Database\Migrations;

class Adapter extends CLI
{
    private Migrations $migrations;

    public function __construct()
    {
        $this->migrations = new Migrations();
        parent::__construct();
    }

    protected function setup(Options $options): void
    {
        $name = config("app.name");
        $options->setHelp("$name console application");

        $options->registerCommand("serve", "Start development server");
        $options->registerCommand(
            "generate:key",
            "Generate secure application key"
        );
        $options->registerCommand("cache:create", "Create the template cache");
        $options->registerCommand("cache:clear", "Clear the template cache");
        $options->registerCommand(
            "migrate:create",
            "Generate a new migration file"
        );
        $options->registerCommand(
            "migrate:list",
            "See migration list and statuses"
        );
        $options->registerCommand(
            "migrate:fresh",
            "WARNING: Drops database and migrates fresh database"
        );
        $options->registerCommand("migrate:up", "Run migration UP method");
        $options->registerCommand("migrate:down", "Run migration DOWN method");

        $this->register($options);
    }

    public function main(Options $options): void
    {
        foreach ($options->getOpt() as $opt => $val) {
            $this->option($opt);
        }
        match ($options->getCmd()) {
            "serve" => $this->serve($options->getArgs()),
            "generate:key" => $this->generateKey(),
            "cache:create" => $this->cacheCreate(),
            "cache:clear" => $this->cacheClear(),
            "migrate:list" => $this->migrateList(),
            "migrate:fresh" => $this->migrateFresh(),
            "migrate:up" => $this->runMigration($options->getArgs(), "up"),
            "migrate:down" => $this->runMigration($options->getArgs(), "down"),
            "migrate:create" => $this->createMigration(
                $options->getArgs()[0] ?? ""
            ),
            default => "",
        };
        $this->command($options->getCmd());
        echo $options->help();
    }

    /**
     * Override
     */
    protected function register(Options $options)
    {
    }

    /**
     * Override
     */
    protected function option(string $option)
    {
    }

    /**
     * Override
     */
    protected function command(string $command)
    {
    }

    private function generateKey(): void
    {
        $unique = uniqid(more_entropy: true);
        $key = `echo -n $unique | openssl dgst -binary -sha256 | openssl base64`;
        $this->success(" Application key: " . $key);
        $this->info(" Add this key to your .env file under APP_KEY");
        exit();
    }

    private function cacheCreate(): void
    {
        $path = config("paths.template-cache");
        // Check if the directory already exists
        if (!is_dir($path)) {
            // Create the directory with recursive option
            if (!mkdir($path, 0775, true) && !is_dir($path)) {
                $this->error(" Failed to create cache directory: {$path}");
            }
        }

        // Change ownership to www-data
        $user = "www-data";
        $group = "www-data";

        if (!chown($path, $user) || !chgrp($path, $group)) {
            $this->error(
                " Failed to set ownership for cache directory: {$path}"
            );
        }

        // Ensure permissions are correct
        if (!chmod($path, 0775)) {
            $this->error(
                " Failed to set permissions for cache directory: {$path}"
            );
        }
        $this->success(" Cache directory created at: {$path}");
        exit();
    }

    private function cacheClear(): void
    {
        $cache = config("paths.template-cache");

        if (is_dir($cache)) {
            exec(
                "find $cache -mindepth 1 ! -name '.gitignore' -type d -exec rm -rf {} +"
            );
        }
        exit();
    }

    private function serve(array $args): void
    {
        $bin_path = config("paths.bin");
        $cmd = $bin_path . "serve";
        if (count($args) === 2) {
            $cmd .= " " . $args[0] . " " . $args[1];
        }
        `bash $cmd`;
        exit();
    }

    private function runMigration(
        array $migration_file,
        string $direction
    ): void {
        if (!isset($migration_file[0])) {
            $this->warning(" No migration file was given...");
            exit();
        }
        $this->info(" Now running database migration...");
        sleep(1);
        $migration = array_filter(
            $this->migrations->mapMigrations(),
            fn($mig) => $mig["name"] === basename($migration_file[0])
        );
        if (!$migration) {
            $this->error(" Migration file doesn't exist?!");
            exit();
        }

        if ($direction === "up") {
            $this->migrateUp($migration);
        } elseif ($direction === "down") {
            $this->migrateDown($migration);
        } else {
            $this->error(" Unknown migration direction?!");
        }
        $this->notice(" Complete!");
        exit();
    }

    private function createMigration(?string $table_name): void
    {
        if (!$table_name) {
            $this->warning("You must provide a table name");
            exit();
        }
        $migration_path = config("paths.migrations");
        $migration_file = $migration_path . time() . "_create_$table_name.php";
        $class = <<<CLASS
<?php
namespace Nebula\Migrations;

use Helios\Database\{Blueprint, Schema, IMigration};

return new class implements IMigration
{
    private \$table = "{table_name}";
    public function up(): string
    {
        return Schema::create(\$this->table, function (Blueprint \$table) {
            \$table->bigIncrements("id");
            \$table->primaryKey("id");
        });
    }

    public function down(): string
    {
        return Schema::drop(\$this->table);
    }
};
CLASS;
        $class = str_replace("{table_name}", $table_name, $class);
        file_put_contents($migration_file, $class);
        $this->notice(" Complete!");
        exit();
    }

    private function migrateList(): void
    {
        $this->info(" Migrations:");
        sleep(1);
        $migrations = $this->migrations->mapMigrations();
        uasort($migrations, fn($a, $b) => $a["name"] <=> $b["name"]);
        foreach ($migrations as $migration) {
            $msg = sprintf(
                "%s ... %s\n",
                $migration["name"],
                $migration["status"]
            );
            switch ($migration["status"]) {
                case "pending":
                    $this->notice($msg);
                    break;
                case "complete":
                    $this->success($msg);
                    break;
                case "failure":
                    $this->alert($msg);
                    break;
            }
        }
        $this->notice(" Complete!");
        exit();
    }

    protected function confirm($prompt = "Are you sure?"): bool
    {
        $this->info($prompt . " (yes/no): ");
        $input = strtolower(trim(fgets(STDIN)));
        return $input === "yes" || $input === "y";
    }

    private function migrateFresh(): void
    {
        if (
            !$this->confirm(
                "Are you sure you want to proceed? This action will drop the database and run migration files, resulting in a complete loss of all current data."
            )
        ) {
            $this->info("Okay, aborting migration.");
            exit();
        }
        $this->info(" Creating a new database...");
        sleep(1);
        $this->migrations->refreshDatabase();
        $migrations = $this->migrations->mapMigrations();
        uasort($migrations, fn($a, $b) => $a["name"] <=> $b["name"]);
        $this->migrateUp($migrations);
        $this->notice(" Complete!");
        exit();
    }

    private function migrateUp(array $migrations): void
    {
        foreach ($migrations as $migration) {
            $class = $migration["class"];
            $name = $migration["name"];
            $hash = $migration["hash"];
            $result = $this->migrations->up($class, $hash);
            if ($result) {
                $this->success(" Migration up: " . $name);
            } elseif (is_null($result)) {
                $this->info(" Migration already exists: " . $name);
            } else {
                $this->error(" Migration error: " . $name);
            }
        }
    }

    private function migrateDown(array $migrations): void
    {
        foreach ($migrations as $migration) {
            $class = $migration["class"];
            $name = $migration["name"];
            $hash = $migration["hash"];
            $result = $this->migrations->down($class, $hash);
            if ($result) {
                $this->success(" Migration down: " . $name);
            } elseif (is_null($result)) {
                $this->info(" Migration already exists: " . $name);
            } else {
                $this->error(" Migration error: " . $name);
            }
        }
    }
}
