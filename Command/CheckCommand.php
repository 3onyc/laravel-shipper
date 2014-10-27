<?php
namespace x3tech\LaravelShipper\Command;

use Illuminate\Console\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Process\Process;

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->checkExecutable('docker');
        $this->checkExecutable('fig');
    }

    protected function checkExecutable($name)
    {
        $this->output->write(str_pad(
            sprintf("<comment>Checking for %s executable... </comment>", $name),
            60
        ));
        
        $exit = (new Process(sprintf('which %s', $name)))->run();
        if ($exit != 0) {
            $this->error(sprintf("not found", $name));
        } else {
            $this->info("present");
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

