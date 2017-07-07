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


class FeatureCommand extends AbstractCommand
{
    protected $allowedActions = array(
        'start',
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

            $question = new Question('Please enter feature name. It can contain only [0-9,a-z,-,_] chars.', false);
            // todo verify allowed chars
            $featureName = $this->getHelper('question')->ask($input, $output, $question);
            if (!$featureName) {
                throw new ExitException('Stop the release process and exit.' . PHP_EOL);
            }

            echo $featureName;

        } catch (ExitException $e) {
            $output->write($e->getMessage());
            return;
        }
    }

}
