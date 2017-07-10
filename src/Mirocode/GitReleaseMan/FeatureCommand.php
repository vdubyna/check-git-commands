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
use Mirocode\GitReleaseMan\AbstractCommand as Command;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Mirocode\GitReleaseMan\ExitException;
use \Github\Client as GithubClient;


class FeatureCommand extends Command
{
    protected $allowedActions = array(
        'start' => 'start',
        'publish' => 'start',
        'pre-release' => 'preRelease',
        'release' => 'release'
    );

    protected $configuration;

    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            //TODO add default config path
            ->setName('git:flow:feature')
            ->addArgument('action', InputArgument::REQUIRED, 'Action')
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Path to config file', '.git-release-man.yml')
            ->setDescription('Make pre-release.')
            ->setHelp('Make pre-release');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $configFilePath = $input->getOption('config');
            $this->configuration = Yaml::parse(file_get_contents($configFilePath));
        } catch (ParseException $e) {
            throw new ExitException("Unable to parse the YAML string: %s", $e->getMessage());
        }

        $action = $input->getArgument('action');

        if (key_exists($action, $this->allowedActions) && method_exists($this, $this->allowedActions[$action])) {
            $this->$action($input, $output);
        }


    }

    public function start(InputInterface $input, OutputInterface $output)
    {
        $originRepoUrl = $this->configuration->github->url;
        $repoNamespace = $this->configuration->github->namespace;
        $baseBranch    = $this->configuration->feature->start->baseBranch;

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

        $githubName           = $this->configuration->github->username;
        $githubRepositoryName = $this->configuration->github->repository->name;
        $githubKey            = $this->configuration->github->token;
        $repoNamespace        = $this->configuration->github->namespace;

        $baseBranch           = $this->configuration->feature->publish->baseBranch;
        $prereleaseLabel      = $this->configuration->feature->publish->reservedLabel;

        try {
            $question = new ConfirmationQuestion('Do you want to publish feature for testing?: ', false);
            if (!$this->getHelper('question')->ask($input, $output, $question)) {
                throw new ExitException('Stop the process and exit.' . PHP_EOL);
            }
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
                    $pr = $client->api('pull_request')->create($githubName, $githubRepositoryName, array(
                        'base'  => $baseBranch,
                        'head'  => $currentBranchName,
                        'title' => 'Test branch',
                        'body'  => 'This is description for test pr.'
                    ));

                    $client->api('issue')->labels()->add($githubName,
                        $githubRepositoryName,
                        $pr['number'],
                        $prereleaseLabel);
                    // TODO show success message

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

    public function preRelease(InputInterface $input, OutputInterface $output)
    {
        // Verify if current branch is master branch
        // switch and prepare to master branch
        // Create feature branch following the pattern

        $githubName           = $this->configuration->github->username;
        $githubRepositoryName = $this->configuration->github->repository->name;
        $githubKey            = $this->configuration->github->token;
        $repoNamespace        = $this->configuration->github->namespace;

        $baseBranch           = $this->configuration->feature->publish->baseBranch;
        $prereleaseLabel      = $this->configuration->feature->publish->reservedLabel;

        try {
            $question = new ConfirmationQuestion('Do you want to publish feature for testing?: ', false);
            if (!$this->getHelper('question')->ask($input, $output, $question)) {
                throw new ExitException('Stop the process and exit.' . PHP_EOL);
            }
            try {
                // reset to base branch
                // merge PR's into base branch
                // create pre-release branch and push into repository.

            } catch (ProcessFailedException $e) {
                $output->write($e->getMessage());
                return;
            }
        } catch (ExitException $e) {
            $output->write($e->getMessage());
            return;
        }
    }

    public function release(InputInterface $input, OutputInterface $output)
    {
        // Verify if current branch is master branch
        // switch and prepare to master branch
        // Create feature branch following the pattern
        $githubName           = $this->configuration->github->username;
        $githubRepositoryName = $this->configuration->github->repository->name;
        $githubKey            = $this->configuration->github->token;
        $repoNamespace        = $this->configuration->github->namespace;

        $baseBranch           = $this->configuration->feature->publish->baseBranch;
        $prereleaseLabel      = $this->configuration->feature->publish->reservedLabel;

        try {
            $question = new ConfirmationQuestion('Do you want to publish feature for testing?: ', false);
            if (!$this->getHelper('question')->ask($input, $output, $question)) {
                throw new ExitException('Stop the process and exit.' . PHP_EOL);
            }
            try {
                // reset to base branch
                // merge PR's ready to prod
                // Prepare pull request
                // merge pull request.
                // Make a release tag
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
