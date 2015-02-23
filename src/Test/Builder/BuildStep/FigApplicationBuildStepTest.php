<?php
namespace x3tech\LaravelShipper\Test\Builder\BuildStep;

use PHPUnit_Framework_TestCase;
use Mockery as m;

use x3tech\LaravelShipper\Builder\BuildStep\FigApplicationBuildStep;
use x3tech\LaravelShipper\Fig\Definition;
use x3tech\LaravelShipper\SupportReporter;

class FigApplicationBuildStepTest extends FigBuildStepTestBase
{
    protected function setUp()
    {
        $this->cfg = include LARAVEL_SHIPPER_ROOT . '/config/config.php';
    }

    /**
     * Create a FigApplicationBuildStep and mock config with environment $env
     *
     * @param string $env Environment for the config mock to return
     *
     * @return FigApplicationBuildStep
     */
    protected function getStep($env)
    {
        $config = m::mock('Illuminate\Config\Repository')
            ->shouldReceive('getEnvironment')
            ->andReturn($env)
            ->shouldReceive('get')
            ->with('shipper::config')
            ->andReturn($this->cfg)
            ->getMock();

        return new FigApplicationBuildStep($config);
    }

    public function testWithoutVolumes()
    {
        $definition = new Definition;

        $this->getStep('production')->run($definition);
        $result = $definition->toArray();

        $this->assertArrayNotHasKey('volumes', $result['app']);
    }

    public function testWithVolumes()
    {
        $definition = new Definition;

        $this->getStep('local')->run($definition);
        $result = $definition->toArray();

        $this->assertArrayHasKey('volumes', $result['app']);
    }

    public function tearDown()
    {
        m::close();
    }
}

