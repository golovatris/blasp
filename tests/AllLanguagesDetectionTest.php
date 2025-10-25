<?php

namespace Blaspsoft\Blasp\Tests;

use Blaspsoft\Blasp\BlaspService;

class AllLanguagesDetectionTest extends TestCase
{
    /**
     * Test profanity detection for all supported languages
     */
    public function test_all_languages_profanity_detection()
    {
        $testCases = [
            'english' => [
                'text' => 'You are a fucking cunt',
                'expected_profanities' => ['fucking', 'cunt'],
                'min_count' => 2
            ],
            'german' => [
                'text' => 'Du bist eine verdammte Fotze',
                'expected_profanities' => ['verdammte', 'fotze'],
                'min_count' => 2
            ],
            'french' => [
                'text' => 'Tu es un putain de connard',
                'expected_profanities' => ['putain', 'connard'],
                'min_count' => 2
            ],
            'spanish' => [
                'text' => 'Eres un maldito hijo de puta',
                'expected_profanities' => ['maldito', 'hijo de puta', 'puta'],
                'min_count' => 2
            ],
            'russian' => [
                'text' => 'Ты ёбанная пизда',
                'expected_profanities' => ['ёбанная', 'пизда'],
                'min_count' => 2
            ]
        ];

        foreach ($testCases as $language => $testCase) {
            echo "\n=== Testing $language ===\n";
            
            // Load language configuration
            $configPath = __DIR__ . "/../config/languages/$language.php";
            $this->assertFileExists($configPath, "Language file not found: $language");
            
            $languageConfig = require $configPath;
            $this->assertArrayHasKey('profanities', $languageConfig, "No profanities array in $language config");
            
            // Create BlaspService with language-specific configuration
            $blaspService = (new BlaspService(
                $languageConfig['profanities'],
                $languageConfig['false_positives'] ?? []
            ))->language($language);
            
            // Test the detection
            $result = $blaspService->check($testCase['text']);
            
            echo "Original: {$testCase['text']}\n";
            echo "Censored: {$result->cleanString}\n";
            echo "Has Profanity: " . ($result->hasProfanity ? 'Yes' : 'No') . "\n";
            echo "Count: {$result->profanitiesCount}\n";
            echo "Found: " . implode(', ', $result->uniqueProfanitiesFound) . "\n";
            
            // Assertions
            $this->assertTrue(
                $result->hasProfanity, 
                "[$language] Failed to detect profanities in: {$testCase['text']}"
            );
            
            $this->assertGreaterThanOrEqual(
                $testCase['min_count'], 
                $result->profanitiesCount,
                "[$language] Expected at least {$testCase['min_count']} profanities, got {$result->profanitiesCount}"
            );
            
            // Verify censoring worked
            foreach ($testCase['expected_profanities'] as $profanity) {
                $this->assertStringNotContainsString(
                    $profanity,
                    mb_strtolower($result->cleanString),
                    "[$language] '$profanity' was not censored"
                );
            }
            
            // Should contain asterisks
            $this->assertStringContainsString(
                '*',
                $result->cleanString,
                "[$language] No asterisks found in censored string"
            );
        }
    }
    
    /**
     * Test each language with variations (case, accents, substitutions)
     */
    public function test_language_variations()
    {
        $variations = [
            'german' => [
                'verdammte' => ['VERDAMMTE', 'Verdammte', 'verdammte', 'VeRdAmMtE'],
                'scheisse' => ['SCHEISSE', 'Scheisse', 'scheisse', 'ScHeIsSe', 'scheiße']
            ],
            'french' => [
                'merde' => ['MERDE', 'Merde', 'merde', 'MeRdE'],
                'putain' => ['PUTAIN', 'Putain', 'putain', 'PuTaIn']
            ],
            'spanish' => [
                'mierda' => ['MIERDA', 'Mierda', 'mierda', 'MiErDa'],
                'joder' => ['JODER', 'Joder', 'joder', 'JoDeR']
            ],
            'english' => [
                'fuck' => ['FUCK', 'Fuck', 'fuck', 'FuCk', 'f@ck', 'f*ck'],
                'shit' => ['SHIT', 'Shit', 'shit', 'ShIt', 'sh1t', 'sh!t']
            ],
            'russian' => [
                'ебаное' => ['ЕБАНОЕ', 'Ебаное', 'ебаное', 'ЕБаНоЕ'],
                'говно' => ['ГОВНО', 'Говно', 'говно', 'ГоВнО']
            ]
        ];
        
        foreach ($variations as $language => $words) {
            echo "\n=== Testing $language variations ===\n";
            
            $languageConfig = require __DIR__ . "/../config/languages/$language.php";
            $blaspService = new BlaspService(
                $languageConfig['profanities'],
                $languageConfig['false_positives'] ?? []
            );
            
            foreach ($words as $base => $variants) {
                foreach ($variants as $variant) {
                    $testText = "This contains $variant here";
                    $result = $blaspService->check($testText);
                    
                    $this->assertTrue(
                        $result->hasProfanity,
                        "[$language] Failed to detect variant '$variant' of '$base'"
                    );
                    
                    echo "  ✓ Detected: '$variant' -> '{$result->cleanString}'\n";
                }
            }
        }
    }
    
    /**
     * Test language-specific normalizers are working
     */
    public function test_language_normalizers()
    {
        // German-specific: umlauts and eszett
        $germanTests = [
            'scheiße' => 'scheisse',  // ß -> ss
            'Scheiße' => 'scheisse',
            'SCHEISSE' => 'scheisse',
            'arschlöcher' => 'arschloecher',  // ö -> oe
        ];
        
        $germanConfig = require __DIR__ . '/../config/languages/german.php';
        $germanBlasp = new BlaspService(
            $germanConfig['profanities'],
            $germanConfig['false_positives'] ?? []
        );
        
        echo "\n=== Testing German normalizers ===\n";
        foreach ($germanTests as $input => $normalized) {
            $result = $germanBlasp->check("Das ist $input test");
            $this->assertTrue(
                $result->hasProfanity,
                "German normalizer failed for '$input' (should normalize to '$normalized')"
            );
            echo "  ✓ '$input' detected and censored\n";
        }
        
        // French-specific: accents
        $frenchTests = [
            'connard' => 'connard',
            'CONNARD' => 'connard',
            'Connard' => 'connard',
        ];
        
        $frenchConfig = require __DIR__ . '/../config/languages/french.php';
        $frenchBlasp = new BlaspService(
            $frenchConfig['profanities'],
            $frenchConfig['false_positives'] ?? []
        );
        
        echo "\n=== Testing French normalizers ===\n";
        foreach ($frenchTests as $input => $normalized) {
            $result = $frenchBlasp->check("C'est un $input ici");
            $this->assertTrue(
                $result->hasProfanity,
                "French normalizer failed for '$input'"
            );
            echo "  ✓ '$input' detected and censored\n";
        }
    }
}