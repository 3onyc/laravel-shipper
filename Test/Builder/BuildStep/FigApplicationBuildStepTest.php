<?php
namespace x3tech\LaravelShipper\Test\Builder\BuildStep;

use PHPUnit_Framework_TestCase;
use Mockery as m;

use x3tech\LaravelShipper\Builder\BuildStep\FigApplicationBuildStep;

class FigApplicationBuildStepTest extends PHPUnit_Framework_TestCase
{
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
            ->andReturn(include __DIR__ . '/../../../config/config.php')
            ->getMock();

        return new FigApplicationBuildStep($config);
    }

    public function testWithoutVolumes()
    {
        $expected = array(
            'app' => array(
                'build' => '.',
                'ports' => array(
                    '8080:80'
                ),
                'environment' => array(
                    'APP_ENV' => 'production'
                ),
                'volumes' => array(),
                'links' => array()
            )
        );

        $structure = $this->getStep('production')->run(array());
        $this->assertEquals($expected, $structure);
    }

    public function testWithVolumes()
    {
        $expected = array(
            'app' => array(
                'build' => '.',
                'ports' => array(
                    '8080:80'
                ),
                'environment' => array(
                    'APP_ENV' => 'local'
                ),
                'volumes' => array(
                    '.:/var/www',
                    './app/storage/logs/hhvm:/var/log/hhvm',
                    './app/storage/logs/nginx:/var/log/nginx'
                ),
                'links' => array()
            )
        );

        $structure = $this->getStep('local')->run(array());
        $this->assertEquals($expected, $structure);
    }

    public function tearDown()
    {
        m::close();
    }
}

