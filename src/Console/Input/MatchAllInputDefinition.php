<?php
namespace x3tech\LaravelShipper\Console\Input;

use Symfony\Component\Console\Input\InputDefinition;

/**
 * An InputDefinition class that captures all arguments and options
 */
class MatchAllInputDefinition extends InputDefinition
{
    public function hasOption($name)
    {
        return true;
    }

    public function hasArgument($nameOrPosition)
    {
        return true;
    }
}
