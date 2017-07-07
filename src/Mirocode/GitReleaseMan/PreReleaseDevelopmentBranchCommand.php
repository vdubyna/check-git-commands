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
use Symfony\Component\Process\Exception\ProcessFailedException;
use Mirocode\GitReleaseMan\Version;
use Mirocode\GitReleaseMan\AbstractCommand;
use Symfony\Component\Yaml\Exception\ParseException;


class PreReleaseDevelopmentBranchCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('git:release-branch')

            // the short description shown while running "php bin/console list"
            ->setDescription('Make pre-release.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Prepare branch for release');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $githubName = 'vdubyna';
        $githubRepositoryName = 'check-git-commands';
        $githubKey = '8c63b2b0c8c082b6dcbd9c04acd86e11613b61b6';

        $originRepoUrl = 'git@github.com:vdubyna/check-git-commands.git';
        $repoNamespace = 'origin';
        $releaseBranch = 'master';
        $versionType = 'rc';

        $client = new \Github\Client();
        $client->authenticate($githubKey, null, \Github\Client::AUTH_URL_TOKEN);
        $issues = $client->api('issue')->all($githubName, $githubRepositoryName, array('state' => 'open', 'labels' => 'IN-BETA'));

        // check if current branch follows the name pattern
        // push current branch to repository
        // find recent pre-release branch
        // make pull request to pre-release branch

        exit;


        // Reset to release branch origin/master
        // clenup branch
        try {
            $this->prepareRepository($input, $output, $repoNamespace, $originRepoUrl, $releaseBranch);
        } catch (ExitException $e) {
            $output->write($e->getMessage());
            return;
        }

        try {
            $dateMark = date('Y-m-d.h-i-s');
            $nextVersion = Version::fromString($this->_getHighestVersion())->increase($versionType, $dateMark);

            $this->_executeShellCommand("git checkout -b {$nextVersion->getVersionWithoutExtraData()}");
            $this->_executeShellCommand("git tag {$nextVersion}");
            $this->_executeShellCommand("git push {$repoNamespace} {$nextVersion}");
        } catch (ProcessFailedException $e) {
            $output->write($e->getMessage());
            return;
        }

        // Merge pre-release branches into pre-release branch and push changes to origin

        $client = new \Github\Client();
        $client->authenticate($githubKey , null, \Github\Client::AUTH_URL_TOKEN);
        $issues = $client->api('issue')->all($githubName, $githubRepositoryName, array('state' => 'open', 'labels' => 'IN-BETA'));

        foreach ($issues as $issue) {
            echo $issue['title'] . PHP_EOL;
        }
    }
}
