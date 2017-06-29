#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Mirocode\GitReleaseMan\ReleaseCommand;

$application = new Application();

$application->add(new ReleaseCommand());
$application->run();