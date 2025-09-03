<?php

namespace Blaspsoft\Blasp\Normalizers;

use Blaspsoft\Blasp\Abstracts\StringNormalizer;

class GermanStringNormalizer extends StringNormalizer
{
    public function normalize(string $string): string
    {
        return $this->normalizeGermanCharacters($string);
    }

    /**
     * Normalize German-specific characters and patterns.
     *
     * @param string $string
     * @return string
     */
    private function normalizeGermanCharacters(string $string): string
    {
        // Define German character mappings - focus on core umlauts and ß
        $germanMappings = [
            // Umlauts to their expanded forms
            'ä' => 'ae', 'Ä' => 'AE',
            'ö' => 'oe', 'Ö' => 'OE',
            'ü' => 'ue', 'Ü' => 'UE',
            
            // Eszett (ß) to double s
            'ß' => 'ss',
        ];

        // Apply German character normalizations
        $normalizedString = strtr($string, $germanMappings);

        // Handle German patterns while preserving case
        $normalizedString = preg_replace_callback('/sch/i', function($matches) {
            $match = $matches[0];
            if ($match === 'SCH') return 'SH';
            if ($match === 'Sch') return 'Sh';
            return 'sh';
        }, $normalizedString);

        return $normalizedString;
    }
}