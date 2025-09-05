<?php

namespace Blaspsoft\Blasp\Tests;

use Blaspsoft\Blasp\BlaspService;
use Blaspsoft\Blasp\Config\ConfigurationLoader;

class MultiLanguageProfanityTest extends TestCase
{
    protected array $languageConfigs;
    protected ConfigurationLoader $configurationLoader;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->configurationLoader = new ConfigurationLoader();
        
        // Load all language configurations
        $this->languageConfigs = [
            'english' => require __DIR__ . '/../config/languages/english.php',
            'spanish' => require __DIR__ . '/../config/languages/spanish.php',
            'german' => require __DIR__ . '/../config/languages/german.php',
            'french' => require __DIR__ . '/../config/languages/french.php',
        ];
    }

    /**
     * Test each language's profanities individually with language-specific BlaspService
     */
    public function test_language_specific_profanities()
    {
        foreach ($this->languageConfigs as $language => $config) {
            // Create a BlaspService instance with this language's profanities
            $blaspService = new BlaspService(
                $config['profanities'],
                $config['false_positives'] ?? []
            );
            
            // Test a subset of profanities for performance
            $testProfanities = array_slice($config['profanities'], 0, 20);
            
            foreach ($testProfanities as $profanity) {
                $testString = "This contains $profanity word";
                $result = $blaspService->check($testString);
                
                $this->assertTrue(
                    $result->hasProfanity,
                    "Failed to detect $language profanity: '$profanity'"
                );
            }
        }
    }

    /**
     * Test English profanities detection
     */
    public function test_english_profanities()
    {
        $blaspService = new BlaspService(
            $this->languageConfigs['english']['profanities'],
            $this->languageConfigs['english']['false_positives'] ?? []
        );
        
        // Test common English profanities
        $testCases = [
            'fuck' => 'This fuck word',
            'shit' => 'This shit happens',
            'ass' => 'What an ass',
            'bitch' => 'Stop being a bitch',
            'damn' => 'Damn it all',
        ];
        
        foreach ($testCases as $profanity => $text) {
            $result = $blaspService->check($text);
            $this->assertTrue($result->hasProfanity, "Failed to detect: $profanity");
        }
    }

    /**
     * Test Spanish profanities detection
     */
    public function test_spanish_profanities()
    {
        $blaspService = new BlaspService(
            $this->languageConfigs['spanish']['profanities'],
            $this->languageConfigs['spanish']['false_positives'] ?? []
        );
        
        // Test common Spanish profanities
        $testCases = [
            'mierda' => 'Esta es una mierda',
            'joder' => 'No quiero joder',
            'coño' => 'Que coño es esto',
            'cabrón' => 'Eres un cabrón',
            'puta' => 'La puta madre',
        ];
        
        foreach ($testCases as $profanity => $text) {
            $result = $blaspService->check($text);
            $this->assertTrue($result->hasProfanity, "Failed to detect Spanish: $profanity");
        }
    }

    /**
     * Test German profanities detection
     */
    public function test_german_profanities()
    {
        $blaspService = new BlaspService(
            $this->languageConfigs['german']['profanities'],
            $this->languageConfigs['german']['false_positives'] ?? []
        );
        
        // Test common German profanities
        $testCases = [
            'scheiße' => 'Das ist scheiße',
            'scheisse' => 'Das ist scheisse',
            'arsch' => 'Du bist ein arsch',
            'ficken' => 'Ich will ficken',
            'verdammt' => 'Verdammt noch mal',
        ];
        
        foreach ($testCases as $profanity => $text) {
            $result = $blaspService->check($text);
            $this->assertTrue($result->hasProfanity, "Failed to detect German: $profanity");
        }
    }

    /**
     * Test French profanities detection
     */
    public function test_french_profanities()
    {
        $blaspService = new BlaspService(
            $this->languageConfigs['french']['profanities'],
            $this->languageConfigs['french']['false_positives'] ?? []
        );
        
        // Test common French profanities
        $testCases = [
            'merde' => 'C\'est de la merde',
            'putain' => 'Putain de merde',
            'connard' => 'Quel connard',
            'salope' => 'Une vraie salope',
            'con' => 'Quel con celui-là',
        ];
        
        foreach ($testCases as $profanity => $text) {
            $result = $blaspService->check($text);
            $this->assertTrue($result->hasProfanity, "Failed to detect French: $profanity");
        }
    }

    /**
     * Test profanities with variations (substitutions, doubling, obscuring)
     */
    public function test_profanity_variations()
    {
        $blaspService = new BlaspService(
            $this->languageConfigs['english']['profanities']
        );
        
        $testCases = [
            'f-u-c-k' => 'obscuring with dashes',
            'ffuucckk' => 'character doubling',
            's.h.i.t' => 'obscuring with dots',
            '@ss' => 'substitution',
        ];
        
        foreach ($testCases as $variation => $description) {
            $result = $blaspService->check("This has $variation in it");
            $this->assertTrue(
                $result->hasProfanity,
                "Failed to detect variation ($description): $variation"
            );
        }
    }

    /**
     * Test case insensitivity
     */
    public function test_case_insensitivity()
    {
        $testCases = [
            'english' => ['FUCK', 'FuCk', 'fUcK'],
            'spanish' => ['MIERDA', 'MiErDa', 'mIeRdA'],
            'german' => ['SCHEISSE', 'ScHeIsSe', 'schEISSE'],
            'french' => ['MERDE', 'MeRdE', 'mErDe'],
        ];
        
        foreach ($testCases as $language => $variations) {
            $blaspService = new BlaspService(
                $this->languageConfigs[$language]['profanities'],
                $this->languageConfigs[$language]['false_positives'] ?? []
            );
            
            foreach ($variations as $variation) {
                $result = $blaspService->check("Word: $variation here");
                $this->assertTrue(
                    $result->hasProfanity,
                    "Failed to detect $language case variation: $variation"
                );
            }
        }
    }

    /**
     * Test false positives are not detected
     */
    public function test_false_positives_not_flagged()
    {
        foreach ($this->languageConfigs as $language => $config) {
            if (!isset($config['false_positives']) || empty($config['false_positives'])) {
                continue;
            }
            
            $blaspService = new BlaspService(
                $config['profanities'],
                $config['false_positives']
            );
            
            // Test safe false positives that are unlikely to contain profanities
            $safeFalsePositives = array_filter($config['false_positives'], function($word) {
                // Only test clearly safe words
                return in_array($word, ['class', 'pass', 'hello', 'school', 'book', 'table', 'chair']);
            });
            
            foreach (array_slice($safeFalsePositives, 0, 5) as $word) {
                $result = $blaspService->check("This contains $word word");
                $this->assertFalse(
                    $result->hasProfanity,
                    "False positive incorrectly detected for $language: $word"
                );
            }
        }
    }

    /**
     * Test profanity detection with mixed content
     */
    public function test_mixed_content_detection()
    {
        $blaspService = new BlaspService(
            $this->languageConfigs['english']['profanities']
        );
        
        // Test clear cases only
        $testCases = [
            'fuck' => true,
            'shit' => true,
            'damn' => true,
            'hello' => false,
            'world' => false,
        ];
        
        foreach ($testCases as $text => $shouldDetect) {
            $result = $blaspService->check($text);
            $this->assertEquals(
                $shouldDetect,
                $result->hasProfanity,
                "Incorrect detection for: $text"
            );
        }
    }

    /**
     * Test comprehensive coverage for each language
     */
    public function test_comprehensive_language_coverage()
    {
        $stats = [];
        
        foreach ($this->languageConfigs as $language => $config) {
            $blaspService = new BlaspService(
                $config['profanities'],
                $config['false_positives'] ?? []
            );
            
            $totalProfanities = count($config['profanities']);
            $detected = 0;
            $failed = [];
            
            // Test ALL profanities but optimize the testing process
            $chunks = array_chunk($config['profanities'], 50);
            
            foreach ($chunks as $chunk) {
                foreach ($chunk as $profanity) {
                    // Use simpler test format for speed
                    $result = $blaspService->check($profanity);
                    if ($result->hasProfanity) {
                        $detected++;
                    } else {
                        $failed[] = $profanity;
                    }
                }
                
                // Manage memory
                gc_collect_cycles();
            }
            
            $detectionRate = ($detected / $totalProfanities) * 100;
            $stats[$language] = [
                'tested' => $totalProfanities,
                'detected' => $detected,
                'rate' => $detectionRate,
                'failed' => array_slice($failed, 0, 5) // First 5 failures
            ];
            
            // Assert at least 90% detection rate
            $this->assertGreaterThanOrEqual(
                90,
                $detectionRate,
                sprintf(
                    "%s: Detection rate %.2f%% (detected %d/%d). Failed: %s",
                    ucfirst($language),
                    $detectionRate,
                    $detected,
                    $totalProfanities,
                    implode(', ', $stats[$language]['failed'])
                )
            );
        }
        
        // Output statistics for review
        foreach ($stats as $language => $stat) {
            echo sprintf(
                "\n%s: %.2f%% detection rate (%d/%d tested)",
                ucfirst($language),
                $stat['rate'],
                $stat['detected'],
                $stat['tested']
            );
        }
    }
}