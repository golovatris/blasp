<?php

namespace Blaspsoft\Blasp\Normalizers;

use Blaspsoft\Blasp\Abstracts\StringNormalizer;

class FrenchStringNormalizer extends StringNormalizer
{

    public function normalize(string $string): string
    {
        return $this->removeFrenchAccents($string);
    }

    /**
     * Remove French accents and special characters
     * 
     * @param string $string
     * @return string
     */
    private function removeFrenchAccents(string $string): string
    {
        // French accent mappings
        $frenchAccents = [
            // Lowercase vowels with accents
            'à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
            
            // Uppercase vowels with accents
            'À' => 'A', 'Â' => 'A', 'Ä' => 'A', 'Á' => 'A',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Ö' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ý' => 'Y', 'Ÿ' => 'Y',
            
            // Cedilla
            'ç' => 'c',
            'Ç' => 'C',
            
            // Ligatures
            'œ' => 'oe',
            'Œ' => 'OE',
            'æ' => 'ae',
            'Æ' => 'AE',
        ];

        return strtr($string, $frenchAccents);
    }
}