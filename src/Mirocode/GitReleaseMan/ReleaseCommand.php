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

class ReleaseCommand extends Command
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
            $this->_executeShellCommand('git fetch --progress origin', $output);
            $this->_executeShellCommand('git log -n1 --pretty=format:%H%x20%s', $output);
            $this->_executeShellCommand('git config remote.origin.url', $output);
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
            $this->_executeShellCommand('git fetch origin', $output);
            $this->_executeShellCommand('git reset --hard origin/master', $output);
            $this->_executeShellCommand('git clean -fd', $output);
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

        $versions = explode(PHP_EOL, $this->_executeShellCommand('git tag -l', $output));
        print_r($versions);
        Semver::sort($versions);

        //$client = new \Github\Client();
        //$client->authenticate('16991e61d491933ead32fd870ac11df9f5d797ee', null, \Github\Client::AUTH_URL_TOKEN);
        ////$issues = $client->api('issue')->find('PaxLabs', 'ecomm-b2b-pax', 'open', 'IN-BETA');
        //$issues = $client->api('issue')->all('PaxLabs', 'ecomm-b2b-pax', array('state' => 'open', 'labels' => 'IN-BETA'));
        //
        //foreach ($issues as $issue) {
        //    echo $issue['title'] . PHP_EOL;
        //}
    }

    /**
     *
     * @param                 $cmd
     * @param OutputInterface $output
     *
     * @return string
     */
    protected function _executeShellCommand($cmd, OutputInterface $output)
    {
        $process = new Process($cmd);
        $process->mustRun();
        $output->write($process->getOutput());

        return $process->getOutput();
    }
}