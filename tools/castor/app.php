<?php

use Castor\Attribute\AsTask;
use function Castor\io;
use function Castor\run;

#[AsTask(name: 'install', namespace: 'hermes', description: 'Install bot')]
function appInstall(): void
{
    io()->note('Installing `app`');
    run('composer install -n --prefer-dist --optimize-autoloader', workingDirectory: 'app/');
}

#[AsTask(name: 'register', namespace: 'hermes', description: 'Register bot commands')]
function appRegister(): void
{
    io()->note('Register bot commands');
    run('bin/console hermes:register', workingDirectory: 'app/');
}

#[AsTask(name: 'start', namespace: 'hermes', description: 'Start the bot')]
function appStart(): void
{
    io()->note('Starting bot');
    run('bin/console hermes:bot -vvv', workingDirectory: 'app/');
}

#[AsTask(name: 'tests', namespace: 'hermes', description: 'Test the bot')]
function appTests(): void
{
    run('bin/phpunit', workingDirectory: 'app/');
}
