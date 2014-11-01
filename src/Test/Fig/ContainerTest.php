<?php
namespace x3tech\LaravelShipper\Test\Fig;

use PHPUnit_Framework_TestCase;

use x3tech\LaravelShipper\Fig\Container;

class ContainerTest extends PHPUnit_Framework_TestCase
{
    public function testGetName()
    {
        $container = new Container('foo');
        $this->assertEquals('foo', $container->getName());
    }

    /**
     * Test that build/image/command/entrypoint aren't in array if they are null
     */
    public function testNotSetWhenEmpty()
    {
        $container = new Container('foo');
        $container->setBuild('.');

        $imageContainer = new Container('foo');
        $imageContainer->setImage('foo/bar');

        $this->assertArrayNotHasKey('image', $container->toArray());
        $this->assertArrayNotHasKey('command', $container->toArray());
        $this->assertArrayNotHasKey('entrypoint', $container->toArray());

        $this->assertArrayNotHasKey('build', $imageContainer->toArray());
    }

    /**
     * Test that things are set correctly when filled
     */
    public function testSetWhenFilled()
    {
        $imageContainer = new Container('foo');
        $imageContainer->setImage('foo/bar');
        $imageContainer->setCommand(array('foo', '--bar'));
        $imageContainer->setEntrypoint('/bin/echo');

        $buildContainer = new Container('bar');
        $buildContainer->setBuild('.');

        $imageArray = $imageContainer->toArray();
        $buildArray = $buildContainer->toArray();

        $this->assertEquals(array('foo', '--bar'), $imageArray['command']);
        $this->assertEquals('/bin/echo', $imageArray['entrypoint']);
        $this->assertEquals('foo/bar', $imageArray['image']);

        $this->assertEquals('.', $buildArray['build']);
    }

    /**
     * Test that only 'image' or 'build' can be set
     */
    public function testImageBuildExclusive()
    {
        $imageContainer = new Container('image');
        $imageContainer->setImage('foo/bar');

        $buildContainer = new Container('build');
        $buildContainer->setBuild('.');

        // Can't set build if image is set
        $this->setExpectedException(
            'InvalidArgumentException',
            'Can only have one of image/build, image already set'
        );
        $imageContainer->setBuild('.');

        // Can't set image if build is set
        $this->setExpectedException(
            'InvalidArgumentException',
            'Can only have one of image/build, build already set'
        );
        $buildContainer->setImage('foo/bar');
    }

    /**
     * Test the flattenLinks method
     */
    public function testFlattenLinks()
    {
        $container = new Container('foo');
        $container->setBuild('.');
        $container->addLink(new Container('bar'));
        $container->addLink(new Container('baz'));

        $array = $container->toArray();
        $this->assertEquals(array('bar', 'baz'), $array['links']);
    }

    /**
     * Test the flattenVolumes method
     */
    public function testFlattenVolumes()
    {
        $container = new Container('foo');
        $container->setBuild('.');
        $container->setVolume('foo', 'bar');
        $array = $container->toArray();

        $this->assertEquals(array('foo:bar'), $array['volumes']);
    }

    /**
     * Test that array contains no name field
     */
    public function testToArrayNoName()
    {
        $container = new Container('foo');
        $container->setBuild('.');

        $array = $container->toArray();
        $this->assertArrayNotHasKey('name', $array);
    }

    public function testToArrayNoBuildImage()
    {
        $container = new Container('foo');

        $this->setExpectedException(
            'InvalidArgumentException',
            'Need to have one of image/build set'
        );
        $array = $container->toArray();
    }

    /**
     * Test that env vars get set, and overridden probably
     */
    public function testEnvironment()
    {
        $container = new Container('foo');
        $container->setBuild('.');
        $container->setEnvironment(array('foo' => 'bar', 'baz' => 'qux'));
        $container->setEnvironment(array('foo' => 'baz'));

        $array = $container->toArray();

        $this->assertEquals(array('foo' => 'baz', 'baz' => 'qux'), $array['environment']);
    }
}
