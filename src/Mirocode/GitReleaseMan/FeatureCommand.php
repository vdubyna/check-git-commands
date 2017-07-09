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
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Console\Input\InputArgument;
use Mirocode\GitReleaseMan\ExitException;
use \Github\Client as GithubClient;


class FeatureCommand extends AbstractCommand
{
    protected $allowedActions = array(
        'start',
        'publish',
    );

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('git:flow:feature')
            ->addArgument('action', InputArgument::REQUIRED, 'Action')
            ->setDescription('Make pre-release.')
            ->setHelp('Make pre-release');
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

        $action = $input->getArgument('action');

        if (in_array($action, $this->allowedActions, true) && method_exists($this, $action)) {
            $this->$action($input, $output);
        }


    }

    public function start(InputInterface $input, OutputInterface $output)
    {
        // Verify if current branch is master branch
        // switch and prepare to master branch
        // Create feature branch following the pattern


        $githubName = 'vdubyna';
        $githubRepositoryName = 'check-git-commands';
        $githubKey = '8c63b2b0c8c082b6dcbd9c04acd86e11613b61b6';

        $originRepoUrl = 'git@github.com:vdubyna/check-git-commands.git';
        $repoNamespace = 'origin';
        $baseBranch = 'development';
        $releaseBranch = 'master';

        $versionType = 'rc';


        // Reset to release branch origin/master
        // clenup branch
        try {
            $this->prepareRepository($input, $output, $repoNamespace, $originRepoUrl, $baseBranch);
            // TODO verify base branch >= release branch

            $question = new Question('Please enter feature name. It can contain only [0-9,a-z,-,_] chars: ', false);
            // todo verify allowed chars
            $featureName = $this->getHelper('question')->ask($input, $output, $question);
            if (!$featureName) {
                throw new ExitException('Stop the release process and exit.' . PHP_EOL);
            }

            try {
                $this->_executeShellCommand("git checkout -b feature-{$featureName}");
            } catch (ProcessFailedException $e) {
                $output->write($e->getMessage());
                return;
            }
        } catch (ExitException $e) {
            $output->write($e->getMessage());
            return;
        }
    }

    public function publish(InputInterface $input, OutputInterface $output)
    {
        // Verify if current branch is master branch
        // switch and prepare to master branch
        // Create feature branch following the pattern


        $githubName = 'vdubyna';
        $githubRepositoryName = 'check-git-commands';
        $githubKey = 'f06d3cab4e132907cfe874fbcfc893c9c029ba19';

        $originRepoUrl = 'git@github.com:vdubyna/check-git-commands.git';
        $repoNamespace = 'origin';
        $baseBranch = 'development';
        $releaseBranch = 'master';

        $versionType = 'rc';
        try {
            //$question = new ConfirmationQuestion('Do you want to publish feature for testing?: ', false);
            //if (!$this->getHelper('question')->ask($input, $output, $question)) {
            //    throw new ExitException('Stop the process and exit.' . PHP_EOL);
            //}
            try {
                $currentBranchName = trim($this->_executeShellCommand("git rev-parse --abbrev-ref HEAD"));
                // TODO check if $currentBranchName is a feature
                $this->_executeShellCommand("git push {$repoNamespace} {$currentBranchName}");
                // Open PR if not opened and Mark it IN-BETA

                $client = new GithubClient();
                $client->authenticate($githubKey , null, GithubClient::AUTH_HTTP_TOKEN);
                // Check if branch has pull request
                $issues = $client->api('pull_request')
                                 ->all($githubName, $githubRepositoryName,
                                     array('state' => 'open', 'type' => 'pr', 'head' => "{$currentBranchName}"));
                if (empty($issues)) {
                    // create pull request
                    // else push recent changes to repository
                    // TODO compile PR description
                    $client->api('pull_request')->create($githubName, $githubRepositoryName, array(
                        'base'  => $baseBranch,
                        'head'  => $currentBranchName,
                        'title' => 'Test branch',
                        'body'  => 'This is description for test pr.'
                    ));

                } else {
                    $this->_executeShellCommand("git push {$repoNamespace} {$currentBranchName}");
                }


            } catch (ProcessFailedException $e) {
                $output->write($e->getMessage());
                return;
            }
        } catch (ExitException $e) {
            $output->write($e->getMessage());
            return;
        }
    }

}
