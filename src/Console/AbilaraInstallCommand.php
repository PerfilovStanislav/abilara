<?php

namespace Abilara\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class AbilaraInstallCommand extends Command
{
    protected $signature = 'abilara:install';

    protected $description = 'Install Abilara package files and generate database migration';

    /**
     * @var \Illuminate\Filesystem\Filesystem
     */
    protected $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle(): int
    {
        $this->info('Starting Abilara installation...');

        $defaultNamespace = 'App\\Modules\\Abilara';
        $namespace = $this->ask('Where would you like to install the Abilara files?', $defaultNamespace);
        
        // Normalize namespace
        $namespace = trim(str_replace('/', '\\', $namespace), '\\');

        // Resolve target directory path
        if (str_starts_with($namespace, 'App\\')) {
            $relativePath = str_replace('\\', '/', substr($namespace, 4));
            $targetDir = app_path($relativePath);
        } else {
            $targetDir = base_path(str_replace('\\', '/', $namespace));
        }

        $this->info("Target directory resolved to: {$targetDir}");

        $this->createDirectory($targetDir);

        // Copy files and replace namespaces
        $files = [
            'Ability.php', 'AbilityTrait.php', 'UserAbilityTrait.php', 'AbilityModel.php',
        ];
        foreach ($files as $file) {
            $this->installFile($targetDir, $namespace, $file);
        }

        $userTable = $this->resolveUserTable();
        $this->publishMigration($userTable);

        $this->info('Abilara installation completed successfully!');
        return Command::SUCCESS;
    }

    protected function createDirectory($targetDir): void
    {
        if (!$this->files->isDirectory($targetDir)) {
            $this->files->makeDirectory($targetDir, 0755, true);
        }
    }

    protected function installFile(string $targetDir, string $namespace, string $file): void
    {
        $source = __DIR__ . "/../$file";
        $destination = $targetDir . "/$file";

        if ($this->files->exists($source)) {
            $content = $this->files->get($source);
            $content = str_replace('Abilara', "$namespace", $content);
            $this->files->put($destination, $content);
            $this->line("Published: " . str_replace(base_path(), '', $destination));
        }
    }

    protected function resolveUserTable()
    {
        $userClass = $this->determineUserClassInteractively();

        if (class_exists($userClass)) {
            try {
                $table = (new $userClass)->getTable();
                $this->info("Resolved User model table: '{$table}' from '{$userClass}'");
                return $table;
            } catch (\Throwable $e) {
                $this->warn("Failed to instantiate User model [{$userClass}]. Falling back to default table 'users'.");
            }
        } else {
            $this->warn("User model class [{$userClass}] does not exist. Falling back to default table 'users'.");
        }

        return 'users';
    }

    protected function determineUserClassInteractively()
    {
        $models = [
            'App\\Models\\User',
            'App\\User',
        ];

        foreach ($models as $model) {
            if (class_exists($model)) {
                if ($this->confirm("Found User model at [{$model}]. Do you want to use it?", true)) {
                    return $model;
                }
                return $this->askForCustomUserClass();
            }
        }

        $this->info("No standard User model found at App\\Models\\User or App\\User.");

        return $this->askForCustomUserClass();
    }

    protected function askForCustomUserClass()
    {
        $class = $this->ask('Please enter the custom User model class path (e.g. App\\Models\\Customer):');
        
        while (empty($class)) {
            $class = $this->ask('Please enter the custom User model class path (e.g. App\\Models\\Customer):');
        }
        
        return trim($class);
    }

    protected function publishMigration($userTable)
    {
        $this->info('Generating database migration...');

        if ($this->migrationExists('create_abilities_table')) {
            $this->warn('A migration for the abilities table already exists, skipping.');
            return;
        }

        $migrationsPath = database_path('migrations');

        // Ensure the migrations directory exists
        if (!$this->files->isDirectory($migrationsPath)) {
            $this->files->makeDirectory($migrationsPath, 0755, true);
        }

        $destinationPath = $this->getMigrationPath($migrationsPath);
        $stubPath = __DIR__ . '/../stubs/create_abilities_table.php.stub';

        if (!$this->files->exists($stubPath)) {
            $this->error('Migration stub not found!');
            return;
        }

        $stubContent = $this->files->get($stubPath);
        $migrationContent = str_replace('{{userTable}}', $userTable, $stubContent);

        $this->files->put($destinationPath, $migrationContent);

        $this->line("Published migration: " . str_replace(base_path(), '', $destinationPath));
    }

    protected function getMigrationPath($migrationsPath): string
    {
        $timestamp = date('Y_m_d_His');
        return rtrim($migrationsPath, '/') . "/{$timestamp}_create_abilities_table.php";
    }

    protected function migrationExists($name): bool
    {
        $path = database_path('migrations');
        
        if (!$this->files->isDirectory($path)) {
            return false;
        }

        $files = $this->files->glob($path . '/*.php');

        foreach ($files as $file) {
            if (Str::contains($file, $name)) {
                return true;
            }
        }

        return false;
    }
}
