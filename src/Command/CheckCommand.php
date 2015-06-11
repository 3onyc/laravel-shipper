<?php
namespace x3tech\LaravelShipper\Command;

use Illuminate\Console\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Process\Process;

use x3tech\LaravelShipper\SupportReporter;

class CheckCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'shipper:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Check if requirements for laravel-shipper are met.";

    /**
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * @var SupportReporter
     */
    protected $supportReporter;

    public function __construct(
        \Illuminate\Config\Repository $config,
        SupportReporter $supportReporter
    ) {
        parent::__construct();

        $this->config = $config;
        $this->supportReporter = $supportReporter;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->checkExecutable('docker');
        $this->checkExecutable('fig');
        $this->checkDatabaseConfig();
        $this->checkQueueConfig();
    }

    protected function checkExecutable($name)
    {
        $this->output->write(str_pad(
            sprintf("<comment>Checking for %s executable... </comment>", $name),
            60
        ));

        $process = new Process(sprintf('which %s', $name));
        $exit = $process->run();

        if ($exit != 0) {
            $this->error("not found");
        } else {
            $this->info("present");
        }
    }

    protected function checkDatabaseConfig()
    {
        $config = $this->config->get('database');
        $default = $config['default'];
        $conn = $config['connections'][$default];

        $this->output->write(str_pad("<comment>Checking database driver... </comment>", 60));

        if (!$this->supportReporter->isSupportedDatabase($conn['driver'])) {
            $this->error(sprintf('driver %s not supported', $conn['driver']));
            return;
        } else {
            $this->info('ok');
        }

        // No further checks for SQLite
        if ($conn['driver'] === 'sqlite') {
            return;
        }

        $this->output->write(str_pad("<comment>Checking database config... </comment>", 60));
        if ($conn['host'] !== 'db') {
            $this->error("Host not set to 'db'");
        } else {
            $this->info('ok');
        }
    }

    protected function checkQueueConfig()
    {
        $config = $this->config->get('queue');
        $default = $config['default'];
        $conn = $config['connections'][$default];

        $this->output->write(str_pad("<comment>Checking queue driver... </comment>", 60));

        if (!$this->supportReporter->isSupportedQueue($conn['driver'])) {
            $this->error(sprintf('driver %s not supported', $conn['driver']));
            return;
        } else {
            $this->info('ok');
        }

        // No further checks for SQLite
        if ($conn['driver'] === 'sync') {
            return;
        }

        $this->output->write(str_pad("<comment>Checking queue config... </comment>", 60));
        if ($conn['host'] !== 'queue') {
            $this->error("Host not set to 'queue'");
        } else {
            $this->info('ok');
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array();
    }

}

