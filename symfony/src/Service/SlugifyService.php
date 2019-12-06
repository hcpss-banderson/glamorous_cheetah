<?php

namespace App\Service;

class SlugifyService
{
    /**
     * Create a slug.
     *
     * @param string $string
     * @return string
     */
    public function slugify(string $string): string
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
    }
}
