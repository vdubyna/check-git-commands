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
        $originRepo = 'git@github.com:vdubyna/check-git-commands.git';
        $originRepoNamespace = 'origin';
        $releaseBranch = 'master';

        // Warn, the action will reset current repository to release branch if not - stop the process
        $question = new ConfirmationQuestion(
            'Confirm to reset the repository to release branch and clean it? (y/n): ', false);

        if (!$this->getHelper('question')->ask($input, $output, $question)) {
            $output->write('Stop the release process and exit.' . PHP_EOL);
            return;
        }

        // get repository info
        try {
            $this->_executeShellCommand("git remote add -f {$originRepoNamespace} {$originRepo}");
            $this->_executeShellCommand("git fetch --progress {$originRepoNamespace}");
            $this->_executeShellCommand("git log -n1 --pretty=format:%H%x20%s");
            $this->_executeShellCommand("git config remote.{$originRepoNamespace}.url");
        } catch (ProcessFailedException $e) {
            $output->write($e->getMessage());
            $output->write('Stop the release process and exit.' . PHP_EOL);
            return;
        }

        // ask to continue and prepare for release
        $question = new ConfirmationQuestion('Do you want to continue? (y/n): ', false);
        if (!$this->getHelper('question')->ask($input, $output, $question)) {
            $output->write('Stop the release process and exit.' . PHP_EOL);
            return;
        }

        try {
            $this->_executeShellCommand("git fetch {$originRepoNamespace}");
            $this->_executeShellCommand("git reset --hard {$originRepoNamespace}/{$releaseBranch}");
            $this->_executeShellCommand("git clean -fd");
        } catch (ProcessFailedException $e) {
            $output->write($e->getMessage());
            $output->write('Stop the release process and exit.' . PHP_EOL);
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

        $nextVersion = Version::fromString($this->_getHighestVersion())->increase('beta');
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