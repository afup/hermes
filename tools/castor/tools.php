<?php

use Castor\Attribute\AsTask;
use function Castor\io;
use function Castor\run;

#[AsTask(namespace: 'tools', description: 'Fix coding style')]
function csFix(): void
{
    run('vendor/bin/php-cs-fixer fix --allow-risky=yes --diff', workingDirectory: 'tools/php-cs-fixer/');
}

#[AsTask(namespace: 'tools', description: 'Check coding style & quality')]
function csCheck(): void
{
    run('vendor/bin/php-cs-fixer fix --allow-risky=yes --dry-run --diff', workingDirectory: 'tools/php-cs-fixer/');
    phpstan();
}

#[AsTask(namespace: 'tools', description: 'Check code with phpstan')]
function phpstan(): void
{
    run('vendor/bin/phpstan analyse', workingDirectory: 'tools/phpstan/');
}

#[AsTask(name: 'install', namespace: 'tools', description: 'Install CS & PHPStan tools')]
function toolsInstall(): void
{
    io()->note('Installing `php-cs-fixer`');
    run('composer install -n --prefer-dist --optimize-autoloader', workingDirectory: 'tools/php-cs-fixer/');

    io()->writeln('');
    io()->note('Installing `phpstan`');
    run('composer install -n --prefer-dist --optimize-autoloader', workingDirectory: 'tools/phpstan/');
}
