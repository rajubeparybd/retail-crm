<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Exception\Configuration\InvalidConfigurationException;
use RectorLaravel\Set\LaravelSetList;
use RectorLaravel\Set\LaravelSetProvider;

try {
    return RectorConfig::configure()
        ->withSetProviders(LaravelSetProvider::class)
        ->withSets([
            LaravelSetList::LARAVEL_TESTING,
        ])
        ->withComposerBased(laravel: true)
        ->withPaths([
            __DIR__ . '/app',
            __DIR__ . '/bootstrap/app.php',
            __DIR__ . '/config',
            __DIR__ . '/database',
            __DIR__ . '/public',
            __DIR__ . '/routes',
        ])
        ->withPreparedSets(
            deadCode: true,
            codeQuality: true,
            typeDeclarations: true,
            privatization: true,
            earlyReturn: true,
        )
        ->withPhpSets()
        ->withParallel();
} catch (InvalidConfigurationException $e) {
    echo 'Rector configuration error: ' . $e->getMessage() . PHP_EOL;
    exit(1);
}
