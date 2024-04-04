<?php

use Castor\Attribute\AsTask;
use function Castor\io;
use function Castor\run;

#[AsTask(name: 'create', namespace: 'database', description: 'Create database')]
function databaseCreate(): void
{
    io()->note('Creating local SQlite database');
    run('bin/console doctrine:database:create', workingDirectory: 'app/');
}

#[AsTask(name: 'reset', namespace: 'database', description: 'Reset database')]
function databaseReset(): void
{
    io()->note('Dropping local SQlite database');
    run('bin/console doctrine:database:drop --if-exists', workingDirectory: 'app/');
    databaseCreate();
}

#[AsTask(name: 'status', namespace: 'database', description: 'Migrate database')]
function databaseStatus(): void
{
    run('bin/console doctrine:migrations:status', workingDirectory: 'app/');
}

#[AsTask(name: 'migrate', namespace: 'database', description: 'Migrate database')]
function databaseMigrate(): void
{
    run('bin/console doctrine:migrations:migrate', workingDirectory: 'app/');
}

#[AsTask(name: 'generate-empty', namespace: 'database', description: 'Generate empty database migration')]
function databaseGenerateEmptyMigration(): void
{
    run('bin/console doctrine:migrations:generate', workingDirectory: 'app/');
}

#[AsTask(name: 'generate-diff', namespace: 'database', description: 'Generate database migration based on entity diff')]
function databaseGenerateMigration(): void
{
    run('bin/console doctrine:migrations:diff', workingDirectory: 'app/');
}
