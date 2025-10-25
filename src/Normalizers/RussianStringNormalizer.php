<?php

namespace Blaspsoft\Blasp\Normalizers;

use Blaspsoft\Blasp\Abstracts\StringNormalizer;

class RussianStringNormalizer extends StringNormalizer
{

    public function normalize(string $string): string
    {
        return $this->removeRussianAccents($string);
    }

    /**
     * Remove Russian accents and special characters
     * 
     * @param string $string
     * @return string
     */
    private function removeRussianAccents(string $string): string
    {
        // Russian accent mappings
        $russianAccents = [
            // Usually replaced characters
            'ё' => 'е', 'э' => 'е',
        ];

        return strtr($string, $russianAccents);
    }
}