<?php
namespace x3tech\LaravelShipper\Test\Builder\BuildStep;

use PHPUnit_Framework_TestCase;
use Mockery as m;

use x3tech\LaravelShipper\Builder\BuildStep\DockerComposeQueueBuildStep;
use x3tech\LaravelShipper\SupportReporter;
use x3tech\LaravelShipper\CompatBridge;

class DockerComposeQueueBuildStepTest extends DockerComposeBuildStepTestBase
{
    protected function setUp()
    {
        $this->cfg = include LARAVEL_SHIPPER_ROOT . '/src/config/config.php';
    }

    /**
     * Create a DockerComposeDatabaseBuildStep and mock config with database driver $driver
     *
     * @param string $driver Database driver for the mock to return
     *
     * @return DockerComposeDatabaseBuildStep
     */
    protected function getStep($driver)
    {
        $app = m::mock('Illuminate\Foundation\Application')
            ->shouldReceive('environment')
            ->andReturn('local')
            ->getMock();

        $config = m::mock('Illuminate\Config\Repository')
            ->shouldReceive('get')
            ->with('shipper', null)
            ->andReturn($this->cfg)
            ->shouldReceive('get')
            ->with('queue', null)
            ->andReturn(array(
                'default' => 'queue',
                'connections' => array(
                    'queue' => array(
                        'driver' => $driver
                    )
                )
            ))
            ->getMock();

        $compat = new CompatBridge('5.0', $app, $config);

        return new DockerComposeQueueBuildStep($compat, new SupportReporter);
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

