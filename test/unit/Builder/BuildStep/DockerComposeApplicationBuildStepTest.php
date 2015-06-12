<?php
namespace x3tech\LaravelShipper\Test\Builder\BuildStep;

use PHPUnit_Framework_TestCase;
use Mockery as m;

use x3tech\LaravelShipper\Builder\BuildStep\DockerComposeApplicationBuildStep;
use x3tech\LaravelShipper\DockerCompose\Definition;
use x3tech\LaravelShipper\SupportReporter;
use x3tech\LaravelShipper\CompatBridge;

class DockerComposeApplicationBuildStepTest extends DockerComposeBuildStepTestBase
{
    protected function setUp()
    {
        $this->cfg = include LARAVEL_SHIPPER_CONFIG . '/config.php';
    }

    /**
     * Create a DockerComposeApplicationBuildStep and mock config with environment $env
     *
     * @param string $env Environment for the config mock to return
     *
     * @return DockerComposeApplicationBuildStep
     */
    protected function getStep($env)
    {
        $app = m::mock('Illuminate\Foundation\Application')
            ->shouldReceive('environment')
            ->andReturn($env)
            ->getMock();

        $compat = new CompatBridge(
            '5.0',
            $app,
            m::mock('Illuminate\Config\Repository')
                ->shouldReceive('get')
                ->with('shipper', null)
                ->andReturn($this->cfg)
                ->getMock()
        );

        return new DockerComposeApplicationBuildStep($compat);
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

