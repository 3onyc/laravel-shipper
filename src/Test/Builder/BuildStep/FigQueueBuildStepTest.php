<?php
namespace x3tech\LaravelShipper\Test\Builder\BuildStep;

use PHPUnit_Framework_TestCase;
use Mockery as m;

use x3tech\LaravelShipper\Builder\BuildStep\FigQueueBuildStep;
use x3tech\LaravelShipper\SupportReporter;

class FigQueueBuildStepTest extends FigBuildStepTestBase
{
    protected function setUp()
    {
        $this->cfg = include LARAVEL_SHIPPER_ROOT . '/config/config.php';
    }

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
            ->andReturn($this->cfg)
            ->shouldReceive('getEnvironment')
            ->andReturn('local')
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

        return new FigQueueBuildStep($config, new SupportReporter);
    }

    public function testBeanstalkd()
    {
        $definition = $this->getDefinition();

        $this->getStep('beanstalkd')->run($definition);
        $result = $definition->toArray();

        $this->assertContains('queue', $result['app']['links']);
    }

    public function testWorker()
    {
        $expected = array(
            '.:/var/www',
            './app/storage/logs/hhvm:/var/log/hhvm',
            './app/storage/logs/nginx:/var/log/nginx'
        );

        $definition = $this->getDefinition();

        $this->getStep('beanstalkd')->run($definition);
        $result = $definition->toArray();

        $this->assertArrayHasKey('worker', $result);
        $this->assertEquals($expected, $result['worker']['volumes']);
    }

    public function testSync()
    {
        $definition = $this->getDefinition();

        $this->getStep('sync')->run($definition);
        $this->assertArrayNotHasKey('worker', $definition->toArray());
    }

    public function testUnsupported()
    {
        $expected = array(
            'app' => array(
            )
        );

        $definition = $this->getDefinition();

        $this->getStep('unsupported')->run($definition);
        $result = $definition->toArray();

        $this->assertArrayNotHasKey('links', $result['app']);
    }

    public function tearDown()
    {
        m::close();
    }
}

