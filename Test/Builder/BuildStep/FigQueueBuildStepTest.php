<?php
namespace x3tech\LaravelShipper\Test\Builder\BuildStep;

use PHPUnit_Framework_TestCase;
use Mockery as m;

use x3tech\LaravelShipper\Builder\BuildStep\FigQueueBuildStep;

class FigQueueBuildStepTest extends PHPUnit_Framework_TestCase
{
    /**
     * Create a FigDatabaseBuildStep and mock config with database driver $driver
     *
     * @param string $driver Database driver for the mock to return
     *
     * @return FigDatabaseBuildStep
     */
    protected function getStep($driver)
    {
        $config = m::mock('Illuminate\Config\Repository')
            ->shouldReceive('get')
            ->with('shipper::config')
            ->andReturn(include __DIR__ . '/../../../config/config.php')
            ->shouldReceive('getEnvironment')
            ->andReturn('production')
            ->shouldReceive('get')
            ->with('queue')
            ->andReturn(array(
                'default' => 'queue',
                'connections' => array(
                    'queue' => array(
                        'driver' => $driver
                    )
                )
            ))
            ->getMock();

        return new FigQueueBuildStep($config);
    }

    public function testBeanstalkd()
    {
        $expected = array('queue');
        $input = array(
            'app' => array(
                'links' => array()
            )
        );

        $structure = $this->getStep('beanstalkd')->run($input);
        $this->assertEquals($expected, $structure['app']['links']);
    }

    public function testWorker()
    {
        $input = array(
            'app' => array(
                'links' => array()
            )
        );

        $structure = $this->getStep('beanstalkd')->run($input);
        $this->assertArrayHasKey('worker', $structure);
    }

    public function testUnsupported()
    {
        $expected = array();
        $input = array(
            'app' => array(
                'links' => array()
            )
        );

        $structure = $this->getStep('unsupported')->run($input);
        $this->assertEquals($expected, $structure['app']['links']);
    }

    public function tearDown()
    {
        m::close();
    }
}

