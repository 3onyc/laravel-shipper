<?php
namespace x3tech\LaravelShipper\Test\Builder\BuildStep;

use PHPUnit_Framework_TestCase;
use Mockery as m;

use x3tech\LaravelShipper\Builder\BuildStep\FigDatabaseBuildStep;
use x3tech\LaravelShipper\Fig\Definition;
use x3tech\LaravelShipper\Fig\Container;

class FigDatabaseBuildStepTest extends FigBuildStepTestBase
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
            ->andReturn(include LARAVEL_SHIPPER_ROOT . '/config/config.php')
            ->shouldReceive('get')
            ->with('database')
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

        return new FigDatabaseBuildStep($config);
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

