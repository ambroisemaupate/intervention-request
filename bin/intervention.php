<?php
define('APP_ROOT', dirname(__DIR__));

use AM\InterventionRequest\Command\GarbageCollectorCommand;
use AM\InterventionRequest\Command\UnlockGarbageCollectorCommand;
use Symfony\Component\Console\Application;

include 'vendor/autoload.php';

$application = new Application();
$application->add(new GarbageCollectorCommand());
$application->add(new UnlockGarbageCollectorCommand());
$application->run();
