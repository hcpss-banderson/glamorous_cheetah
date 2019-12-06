<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    /**
     * {@inheritDoc}
     * @see \Twig\Extension\AbstractExtension::getFunctions()
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('class', [$this, 'getClassName']),
        ];
    }

    /**
     * Get class name from an object.
     *
     * @param mixed $object
     * @return string
     */
    public function getClassName($object)
    {
        return (new \ReflectionClass($object))->getShortName();
    }
}
