<?php

namespace Blaspsoft\Blasp\Normalizers;

use Blaspsoft\Blasp\Abstracts\StringNormalizer;

class SpanishStringNormalizer extends StringNormalizer
{
    public function normalize(string $string): string
    {
        return $this->normalizeSpanishCharacters($string);
    }

    /**
     * Normalize Spanish-specific characters and patterns.
     *
     * @param string $string
     * @return string
     */
    private function normalizeSpanishCharacters(string $string): string
    {
        // Define Spanish character mappings - focus on core accent removal
        $spanishMappings = [
            // Accented vowels
            'á' => 'a', 'Á' => 'A',
            'é' => 'e', 'É' => 'E',
            'í' => 'i', 'Í' => 'I',
            'ó' => 'o', 'Ó' => 'O',
            'ú' => 'u', 'Ú' => 'U',
            'ü' => 'u', 'Ü' => 'U',
            
            // Ñ character
            'ñ' => 'n', 'Ñ' => 'N',
        ];

        // Apply Spanish character normalizations
        $normalizedString = strtr($string, $spanishMappings);

        // Handle Spanish patterns while preserving case - only at word boundaries or followed by vowels
        $normalizedString = preg_replace_callback('/\bll(?=[aeiouáéíóúü])/i', function($matches) {
            $match = $matches[0];
            if ($match === 'LL') return 'Y';
            if ($match === 'Ll') return 'Y';
            return 'y';
        }, $normalizedString);
        
        $normalizedString = preg_replace_callback('/rr/i', function($matches) {
            $match = $matches[0];
            if ($match === 'RR') return 'R';
            if ($match === 'Rr') return 'R';
            return 'r';
        }, $normalizedString);

        return $normalizedString;
    }
}