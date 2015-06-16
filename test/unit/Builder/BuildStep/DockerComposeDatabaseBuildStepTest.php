<?php
namespace x3tech\LaravelShipper\Test\Builder\BuildStep;

use PHPUnit_Framework_TestCase;
use Mockery as m;

use x3tech\LaravelShipper\Builder\BuildStep\DockerComposeDatabaseBuildStep;
use x3tech\LaravelShipper\DockerCompose\Definition;
use x3tech\LaravelShipper\DockerCompose\Container;
use x3tech\LaravelShipper\SupportReporter;
use x3tech\LaravelShipper\CompatBridge;

class DockerComposeDatabaseBuildStepTest extends DockerComposeBuildStepTestBase
{
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
            ->andReturn('production')
            ->getMock();

        $config = m::mock('Illuminate\Config\Repository')
            ->shouldReceive('get')
            ->with('shipper', null)
            ->andReturn(include LARAVEL_SHIPPER_CONFIG . '/config.php')
            ->getMock()
            ->shouldReceive('get')
            ->with('database', null)
            ->andReturn(array(
                'default' => 'db',
                'connections' => array(
                    'db' => array(
                        'driver' => $driver,
                        'password' => 'foo',
                        'username' => 'bar',
                        'database' => 'foobar'
                    )
                )
            ))
            ->getMock();
        $view = null;
        $blade = m::mock('Illuminate\View\Compilers\BladeCompiler');

        $compat = new CompatBridge('5.0', $app, $config, $view, $blade);

        return new DockerComposeDatabaseBuildStep($compat, new SupportReporter);
    }

    public function testMysql()
    {
        $definition = $this->getDefinition();

        $this->getStep('mysql')->run($definition);

        $result = $definition->toArray();
        $this->assertContains('db', $result['app']['links']);
    }

    public function testUnsupported()
    {
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

