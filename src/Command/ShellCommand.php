<?php
namespace x3tech\LaravelShipper\Command;

use Illuminate\Console\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Process\Process;

use x3tech\LaravelShipper\Console\Input\MatchAllInputDefinition;

class ShellCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'shipper:shell';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Opens a shell connected to the running application";

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $projectName = $this->option('project-name');
        if (!$this->supportsPCNTL()) {
            $this->showUnsupportedMessage($projectName);
            return 1;
        }

        pcntl_exec('/usr/bin/docker-compose', $this->getComposeArguments($projectName));
    }

    protected function showUnsupportedMessage($projectName = null)
    {
        $this->error('OS does not support PCNTL (reqired for shipper:shell)');
        $this->error(' You can manually get a shell by running');
        $this->error(sprintf(
            'docker-compose %s',
            implode(' ', $this->getComposeArguments($projectName))
        ));
    }

    protected function getComposeArguments($projectName = null)
    {
        $args = array();
        if ($projectName !== null) {
            $args[] = sprintf('--project-name=%s', $projectName);
        }

        # TODO: run as www-data or www
        return array_merge($args, array(
            'run', '--rm', '--user=www-data', 'app', '/bin/bash'
        ));
    }

    protected function supportsPCNTL()
    {
        return false;
        return strpos(php_uname('s'), 'Linux') !== false;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array(
                'project-name',
                'p',
                InputOption::VALUE_OPTIONAL,
                'Specify an alternate project name (default: directory name)',
                null
            )
        );
    }
}
