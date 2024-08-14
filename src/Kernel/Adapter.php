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
            "migrate:list" => $this->migrateList(),
            "migrate:fresh" => $this->migrateFresh(),
            "migrate:up" => $this->runMigration($options->getArgs(), "up"),
            "migrate:down" => $this->runMigration($options->getArgs(), "down"),
            default => "",
        };
        $this->command($options->getCmd());
        echo $options->help();
    }

    /**
     * Override
     */
    protected function register(Options $options) {}

    /**
     * Override
     */
    protected function option(string $option) {}

    /**
     * Override
     */
    protected function command(string $command) {}

    private function generateKey(): void
    {
        $unique = uniqid(more_entropy: true);
        $key = `echo -n $unique | openssl dgst -binary -sha256 | openssl base64`;
        $this->success(" Application key: " . $key);
        $this->info(" Add this key to your .env file under APP_KEY");
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

    private function migrateList(): void
    {
        $this->info(" Migrations:");
        sleep(1);
        $migrations = $this->migrations->mapMigrations();
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
        return $input === "yes" || $input === 'y';
    }

    private function migrateFresh(): void
    {
        if (!$this->confirm("Are you sure you want to proceed? This action will drop the database and run migration files, resulting in a complete loss of all current data.")) {
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
