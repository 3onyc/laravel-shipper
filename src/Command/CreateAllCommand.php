<?php
namespace x3tech\LaravelShipper\Command;

use Illuminate\Console\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CreateAllCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'shipper:create:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Create Dockerfile, docker-compose.yml and volume dirs";

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
    $this->call('shipper:create:docker-compose');
        $this->call('shipper:create:docker');
        $this->call('shipper:create:dirs');

        $this->info("All done, call 'docker-compose build && docker-compose up' and start coding!");
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
