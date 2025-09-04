<?php

namespace Blaspsoft\Blasp\Tests;

use Blaspsoft\Blasp\Facades\Blasp;
use Blaspsoft\Blasp\BlaspService;

class AllLanguagesApiTest extends TestCase
{
    public function test_all_languages_detection()
    {
        // Test English profanity
        $result = Blasp::allLanguages()->check('This is fucking amazing');
        $this->assertTrue($result->hasProfanity());
        $this->assertEquals('This is ******* amazing', $result->getCleanString());

        // Test Spanish profanity
        $result = Blasp::allLanguages()->check('esto es una mierda');
        $this->assertTrue($result->hasProfanity());
        $this->assertEquals('esto es una ******', $result->getCleanString());

        // Test German profanity
        $result = Blasp::allLanguages()->check('das ist scheiße');
        $this->assertTrue($result->hasProfanity());
        $this->assertEquals('das ist *******', $result->getCleanString());

        // Test French profanity
        $result = Blasp::allLanguages()->check('c\'est de la merde');
        $this->assertTrue($result->hasProfanity());
        $this->assertEquals('c\'est de la *****', $result->getCleanString());
    }

    public function test_mixed_language_content()
    {
        // Text containing profanities from multiple languages
        $result = Blasp::allLanguages()->check('This shit is mierda and scheiße');
        $this->assertTrue($result->hasProfanity());
        $this->assertEquals('This **** is ****** and *******', $result->getCleanString());
        $this->assertEquals(3, $result->getProfanitiesCount());
    }

    public function test_chainable_all_languages()
    {
        // Test all languages check
        $result = Blasp::allLanguages()->check('damn merde');
        $this->assertTrue($result->hasProfanity());
    }

    public function test_language_shortcuts_vs_all()
    {
        $text = 'fucking merde scheiße mierda';
        
        // Individual language checks
        $englishResult = Blasp::english()->check($text);
        $this->assertEquals(1, $englishResult->getProfanitiesCount()); // Only 'fucking'
        
        // All languages check
        $allResult = Blasp::allLanguages()->check($text);
        $this->assertEquals(4, $allResult->getProfanitiesCount()); // All profanities
        
        // Verify all profanities are masked (check for asterisks)
        $this->assertStringNotContainsString('fucking', $allResult->getCleanString());
        $this->assertStringNotContainsString('merde', $allResult->getCleanString());
        $this->assertStringNotContainsString('scheiße', $allResult->getCleanString());
        $this->assertStringContainsString('*******', $allResult->getCleanString()); // fucking masked
    }

    public function test_direct_service_all_languages()
    {
        $service = new BlaspService();
        $result = $service->allLanguages()->check('This fuck is merde');
        $this->assertTrue($result->hasProfanity());
        $this->assertEquals(2, $result->getProfanitiesCount());
    }

    public function test_configure_with_all_languages()
    {
        // Custom configuration should still work with all languages
        $result = Blasp::allLanguages()
            ->configure(['customword'], ['notbad'])
            ->check('customword and fuck');
        
        $this->assertTrue($result->hasProfanity());
        $this->assertStringContainsString('**********', $result->getCleanString());
    }
}