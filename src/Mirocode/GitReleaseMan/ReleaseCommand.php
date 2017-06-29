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
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion(
            'Confirm to reset the repository to release branch and clean it? (y/n): ', false);

        if (!$helper->ask($input, $output, $question)) {
            $output->write('Stop the release process and exit.' . PHP_EOL);
            return;
        }

        echo 'continue';

        $this->_executeShellCommand('git fetch --progress origin', $output);
        $this->_executeShellCommand('git log -n1 --pretty=format:%H%x20%s', $output);
        $this->_executeShellCommand('git config remote.origin.url', $output);


        $this->_executeShellCommand('git fetch origin', $output);
        $this->_executeShellCommand('git reset --hard origin/master', $output);
        $this->_executeShellCommand('git clean -fdx', $output);


        // Generate release branch
        //

        // Get list of branches to merge and release.

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
     * @param OutputInterface $output
     */
    protected function _executeShellCommand($cmd, OutputInterface $output)
    {
        $process = new Process($cmd);
        try {
            $process->mustRun();
            $output->write($process->getOutput());
        } catch (ProcessFailedException $e) {
            $output->write($e->getMessage());
        }
    }
}