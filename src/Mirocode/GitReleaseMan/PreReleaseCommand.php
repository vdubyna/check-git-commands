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
use Mirocode\GitReleaseMan\Version;
use Mirocode\GitReleaseMan\AbstractCommand;

class PreReleaseCommand extends AbstractCommand
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('git:pre-release')

            // the short description shown while running "php bin/console list"
            ->setDescription('Make pre-release.')

            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('Make pre-release');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $originRepo = 'git@github.com:vdubyna/check-git-commands.git';
        $originRepoNamespace = 'origin';
        $releaseBranch = 'master';


        // Reset to release branch origin/master
        // clenup branch
        // make pre-release branch
        // detect branches to release
        // merge branches to release
        // make pre-release tag and branch
        // push pre-release tag and branch
        // verify pre-relelase branches and remove old ones.



        //$client = new \Github\Client();
        //$client->authenticate('16991e61d491933ead32fd870ac11df9f5d797ee', null, \Github\Client::AUTH_URL_TOKEN);
        ////$issues = $client->api('issue')->find('PaxLabs', 'ecomm-b2b-pax', 'open', 'IN-BETA');
        //$issues = $client->api('issue')->all('PaxLabs', 'ecomm-b2b-pax', array('state' => 'open', 'labels' => 'IN-BETA'));
        //
        //foreach ($issues as $issue) {
        //    echo $issue['title'] . PHP_EOL;
        //}
    }
}
