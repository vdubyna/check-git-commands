<?php
/**
 * Created by PhpStorm.
 * User: vdubyna
 * Date: 6/17/17
 * Time: 12:07
 */

namespace Mirocode\GitReleaseMan;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Mirocode\GitReleaseMan\Version;
use Mirocode\GitReleaseMan\AbstractCommand;

class ReleaseCommand extends AbstractCommand
{

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('git:release')

            // the short description shown while running "php bin/console list"
            ->setDescription('Make release.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Make release');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $originRepoUrl = 'git@github.com:vdubyna/check-git-commands.git';
        $originRepoNamespace = 'origin';
        $releaseBranch = 'master';
        $versionType = 'minor';

        try {
            $this->prepareRepository($input, $output, $originRepoNamespace, $originRepoUrl, $releaseBranch);
        } catch (ExitException $e) {
            $output->write($e->getMessage());
            return;
        }

        // Get current release version
        // Make new tag or branch
        // Push to repository
        // ask to continue and prepare for release
        $question = new ConfirmationQuestion('Do you want to continue? (y/n): ', false);
        if (!$this->getHelper('question')->ask($input, $output, $question)) {
            $output->write('Stop the release process and exit.' . PHP_EOL);
            return;
        }

        $nextVersion = Version::fromString($this->_getHighestVersion())->increase($versionType);
        $nextVersion = 'v' . $nextVersion;

        try {
            $this->_executeShellCommand("git tag {$nextVersion}");
            $this->_executeShellCommand("git push {$originRepoNamespace} {$nextVersion}");
            $output->write('New version ' . $nextVersion . ' was released' . PHP_EOL);
        } catch (ProcessFailedException $e) {
            $output->write($e->getMessage());
            $output->write('Stop the release process and exit.' . PHP_EOL);
            return;
        }
    }
}