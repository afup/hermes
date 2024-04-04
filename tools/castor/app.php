<?php

use Castor\Attribute\AsTask;
use function Castor\io;
use function Castor\run;

#[AsTask(name: 'install', namespace: 'app', description: 'Install bot')]
function botInstall(): void
{
    io()->note('Installing `app`');
    run('composer install -n --prefer-dist --optimize-autoloader', workingDirectory: 'app/');
}

#[AsTask(name: 'start', namespace: 'app', description: 'Start the bot')]
function botStart(): void
{
    io()->note('Starting bot');
    io()->error('TO IMPLEMENT'); // @FIXME
}
