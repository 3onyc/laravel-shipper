<?php
namespace x3tech\LaravelShipper\Command;

use Illuminate\Console\Command;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CreateDirsCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'shipper:create:dirs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Create volume directories";

    /**
     * @var Illuminate\Config\Repository
     */
    protected $config;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        \Illuminate\Config\Repository $config
    ) {
        parent::__construct();

        $this->config = $config;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->info("Creating volume directories...");

        $cfg = $this->config->get('shipper');
        foreach ($cfg['volumes'] as $volume) {
            $src = $this->getSource($volume);

            if ($this->shouldSkipSource($src)) {
                continue;
            }

            $src = $this->stripPrefix($src);
            $path = sprintf('%s/%s', base_path(), $src); // Assemble full path
            $this->createIfNotExists($path, $src);
        }
    }

    /**
     * Get source portion of a volume
     *
     * @param string $volume
     *
     * @return string
     */
    protected function getSource($volume)
    {
        list($src) = explode(':', $volume);
        return $src;
    }

    /**
     * Return true if $src should be skipped
     *
     * Skips empty $src and root of project
     *
     * @param string $src
     *
     * @return bool
     */
    protected function shouldSkipSource($src)
    {
        return empty($src) || $src === './' || $src === '.';
    }


    /**
     * Removes the ./ prefix from $src if it exists
     *
     * @param string $src
     *
     * @return string $src without ./ prefix
     */
    protected function stripPrefix($src)
    {
        if (strpos($src, './') === 0) {
            $src = substr($src, 2);
        }

        return $src;
    }


    /**
     * Create $path if it doesn't exist
     *
     * @param string $path Path to create if it doesn't exist
     * @param string $src Original volume source, used for display purposes
     */
    protected function createIfNotExists($path, $src)
    {
        if (!is_dir($path)) {
            $this->comment(sprintf("Creating: %s", $src));
            mkdir($path, 0700, true);
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

