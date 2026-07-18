<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Command\Command as CommandAlias;

final class DevelopmentSetupCommand extends Command
{
    protected $signature = 'development:setup';

    protected $description = 'Setup the development environment';

    public function handle(): int
    {
        $this->info('Setting up the development environment...');

        $this->info('clearing optimize cache...');
        $this->call('optimize:clear');

        $tempDir = base_path('.temp');
        $ideHelperDir = base_path('.temp/ide-helper');

        if (File::exists($tempDir)) {
            $this->info('Deleting .temp directory...');
            File::deleteDirectory($tempDir);
        }

        $this->info('Creating .temp directory...');
        File::ensureDirectoryExists($ideHelperDir);

        $this->info('Creating storage link...');
        $this->call('storage:link', ['--force' => true]);

        return CommandAlias::SUCCESS;
    }
}
