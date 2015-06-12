<?php
namespace x3tech\LaravelShipper\Command;

use Illuminate\Console\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Process\Process;

use x3tech\LaravelShipper\SupportReporter;
use x3tech\LaravelShipper\CompatBridge;

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
     * @var x3tech\LaravelShipper\CompatBridge
     */
    protected $compat;

    /**
     * @var SupportReporter
     */
    protected $supportReporter;

    public function __construct(
        CompatBridge $compat,
        SupportReporter $supportReporter
    ) {
        parent::__construct();

        $this->compat = $compat;
        $this->supportReporter = $supportReporter;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $passedChecks = 0;

        $passedChecks += $this->checkExecutable('docker');
        $passedChecks += $this->checkExecutable('docker-compose');
        $passedChecks += $this->checkDatabaseConfig();
        $passedChecks += $this->checkQueueConfig();

        return $passedChecks == 4 ? 0 : 1;
    }

    protected function checkExecutable($name)
    {
        $this->output->write(str_pad(
            sprintf("<comment>Checking for %s executable... </comment>", $name),
            65
        ));

        $process = new Process(sprintf('which %s', $name));
        $exit = $process->run();

        if ($exit != 0) {
            $this->error("not found");
            return false;
        } else {
            $this->info("present");
            return true;
        }
    }

    protected function checkDatabaseConfig()
    {
        $cfg = $this->compat->getConfig('database');
        $default = $cfg['default'];
        $conn = $cfg['connections'][$default];

        $this->output->write(str_pad("<comment>Checking database driver... </comment>", 65));

        if (!$this->supportReporter->isSupportedDatabase($conn['driver'])) {
            $this->error(sprintf('driver %s not supported', $conn['driver']));
            return false;
        } else {
            $this->info('ok');
        }

        // No further checks for SQLite
        if ($conn['driver'] === 'sqlite') {
            return true;
        }

        $this->output->write(str_pad("<comment>Checking database config... </comment>", 65));
        if ($conn['host'] !== 'db') {
            $this->error("Host not set to 'db'");
            return false;
        } else {
            $this->info('ok');
            return true;
        }
    }

    protected function checkQueueConfig()
    {
        $cfg = $this->compat->getConfig('queue');
        $default = $cfg['default'];
        $conn = $cfg['connections'][$default];

        $this->output->write(str_pad("<comment>Checking queue driver... </comment>", 65));

        if (!$this->supportReporter->isSupportedQueue($conn['driver'])) {
            $this->error(sprintf('driver %s not supported', $conn['driver']));
            return false;
        } else {
            $this->info('ok');
        }

        // No further checks for sync driver
        if ($conn['driver'] === 'sync') {
            return true;
        }

        $this->output->write(str_pad("<comment>Checking queue config... </comment>", 65));
        if ($conn['host'] !== 'queue') {
            $this->error("Host not set to 'queue'");
            return false;
        } else {
            $this->info('ok');
            return true;
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

