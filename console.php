#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Mirocode\GitReleaseMan\ReleaseCommand;
use Mirocode\GitReleaseMan\PreReleaseCommand;
use Mirocode\GitReleaseMan\PreReleaseGenerateCommand;
use Mirocode\GitReleaseMan\PreReleaseDevelopmentBranchCommand;

$application = new Application();

$application->add(new ReleaseCommand());
$application->add(new PreReleaseCommand());
$application->add(new PreReleaseGenerateCommand());
$application->add(new PreReleaseDevelopmentBranchCommand());
$application->run();