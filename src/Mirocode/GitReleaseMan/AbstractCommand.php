<?php
/**
 * Created by PhpStorm.
 * User: vdubyna
 * Date: 6/17/17
 * Time: 12:07
 */

namespace Mirocode\GitReleaseMan;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Composer\Semver\Semver;
use Mirocode\GitReleaseMan\Version;

class AbstractCommand extends Command
{
    /**
     *
     * @param                 $cmd
     *
     * @return string
     */
    protected function _executeShellCommand($cmd)
    {
        $process = new Process($cmd);
        $process->mustRun();

        return $process->getOutput();
    }

    /**
     * @return array
     */
    protected function _getSortedVersionsArray()
    {
        $versions = explode(PHP_EOL, $this->_executeShellCommand("git tag -l"));
        $versions = array_filter($versions, 'strlen');
        $versions = Semver::sort($versions);

        return $versions;
    }

    /**
     * @return mixed
     */
    protected function _getHighestVersion()
    {
        $versions = $this->_getSortedVersionsArray();

        $version = end($versions);

        return $version;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param                 $originRepoNamespace
     * @param                 $originRepoUrl
     * @param                 $baseBranch
     *
     * @return void
     * @throws ExitException
     */
    protected function prepareRepository(
        InputInterface $input,
        OutputInterface $output,
        $originRepoNamespace,
        $originRepoUrl,
        $baseBranch
    ) {
        $question = new ConfirmationQuestion(
            "Confirm to reset the repository to base branch {$baseBranch} tracked from {$originRepoUrl} and clean it? (y/n): ", false);

        if (!$this->getHelper('question')->ask($input, $output, $question)) {
            throw new ExitException('Stop the release process and exit.' . PHP_EOL);
        }

        try {
            // get repository info
            $remoteRepos = explode(PHP_EOL, $this->_executeShellCommand("git remote"));
            $remoteRepos = array_filter($remoteRepos, 'strlen');
            if (array_search($originRepoNamespace, $remoteRepos) !== false) {
                $this->_executeShellCommand("git remote rm {$originRepoNamespace}");
            }
            $this->_executeShellCommand("git remote add {$originRepoNamespace} {$originRepoUrl}");
            $this->_executeShellCommand("git fetch --progress {$originRepoNamespace}");
            $lastComments = $this->_executeShellCommand("git log -n1 --pretty=format:%H%x20%s");
            $output->write($lastComments . PHP_EOL);
            $configRemoteValue = $this->_executeShellCommand("git config remote.{$originRepoNamespace}.url");
            $output->write($configRemoteValue , PHP_EOL);
            $statusResult = $this->_executeShellCommand("git status");
        } catch (ProcessFailedException $e) {
            throw new ExitException($e);
        }

        // ask to continue and prepare for release
        $question = new ConfirmationQuestion("Do you want to continue and switch the branch to {$baseBranch} " .
            "all changes will be reverted and new files/directories deleted except the ignored " .
            "see the list of files to reset below? " .
            PHP_EOL . "{$statusResult}" . PHP_EOL .
            "(y/n): ", false);
        if (!$this->getHelper('question')->ask($input, $output, $question)) {
            throw new ExitException('Stop process and exit.' . PHP_EOL);
        }

        try {
            $this->_executeShellCommand("git fetch {$originRepoNamespace}");
            $this->_executeShellCommand("git reset --hard {$originRepoNamespace}/{$baseBranch}");
            $this->_executeShellCommand("git clean -fd");
        } catch (ProcessFailedException $e) {
            throw new ExitException('Stop the process and exit.' . PHP_EOL);
        }
    }
}