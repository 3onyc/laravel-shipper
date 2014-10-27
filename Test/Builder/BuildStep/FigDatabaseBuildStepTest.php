<?php
namespace x3tech\LaravelShipper\Test\Builder\BuildStep;

use PHPUnit_Framework_TestCase;
use Mockery as m;

use x3tech\LaravelShipper\Builder\BuildStep\FigDatabaseBuildStep;

class FigDatabaseBuildStepTest extends PHPUnit_Framework_TestCase
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
        $expected = array('db');
        $input = array(
            'app' => array(
                'links' => array()
            )
        );

        $structure = $this->getStep('mysql')->run($input);
        $this->assertEquals($expected, $structure['app']['links']);
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

