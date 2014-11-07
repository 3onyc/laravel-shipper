<?php
namespace x3tech\LaravelShipper;

use x3tech\LaravelShipper\Builder\BuildStep\FigDatabaseBuildStep;
use x3tech\LaravelShipper\Builder\BuildStep\FigQueueBuildStep;

class SupportReporter
{
    /**
     * @var \Illuminate\Config\Repository
     */
    protected $config;

    /**
     * @var array
     */
    protected $supportedDatabases;
    /**
     * @var array
     */
    protected $supportedQueues;

    public function __construct()
    {
        $this->supportedDatabases = array('sqlite');
        $this->supportedQueues = array('sync');
    }

    public function addSupportedDatabase($driver)
    {
        $this->supportedDatabases[] = $driver;
    }

    public function addSupportedQueue($driver)
    {
        $this->supportedQueues[] = $driver;
    }

    public function isSupportedDatabase($driver)
    {
        return in_array($driver, $this->supportedDatabases);
    }

    public function isSupportedQueue($driver)
    {
        return in_array($driver, $this->supportedQueues);
    }
}
