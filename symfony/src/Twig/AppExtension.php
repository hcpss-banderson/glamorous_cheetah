<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;

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
     * {@inheritDoc}
     * @see \Twig\Extension\AbstractExtension::getFilters()
     */
    public function getFilters()
    {
        return [
            new TwigFilter('phone', [$this, 'formatPhone']),
        ];
    }

    /**
     * Format a phone number.
     *
     * @param string $phone
     * @return string
     */
    public function formatPhone($phone)
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strpos($phone, '1') === 0) {
            $phone = substr($phone, 1);
        }

        if (strlen($phone) !== 10) {
            // Seems like an invalid phone number.
            return '';
        }

        $areaCode = substr($phone, 0, 3);
        $prefix = substr($phone, 3, 3);
        $lineNumber = substr($phone, 6);

        return "+1 ({$areaCode}) {$prefix}-{$lineNumber}";
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
