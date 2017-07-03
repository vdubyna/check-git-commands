<?php
/**
 * Created by PhpStorm.
 * User: vdubyna
 * Date: 6/17/17
 * Time: 12:07
 */

namespace Mirocode\GitReleaseMan;

use Symfony\Component\Console\Command\Command;
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
}