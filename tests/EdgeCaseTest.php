<?php

namespace Blaspsoft\Blasp\Tests;

use Blaspsoft\Blasp\Facades\Blasp;

class EdgeCaseTest extends TestCase
{
    public function test_fuckme_not_detected_across_word_boundaries()
    {
        // The problematic case: "fuck merde" should not trigger "fuckme"
        $result = Blasp::allLanguages()->check('fuck merde scheiße mierda');
        
        // Should detect individual profanities but NOT "fuckme"
        $this->assertTrue($result->hasProfanity());
        $this->assertNotContains('fuckme', $result->getUniqueProfanitiesFound());
        
        // Should detect the actual profanities
        $found = $result->getUniqueProfanitiesFound();
        $this->assertContains('fuck', $found);
        $this->assertContains('merde', $found);
        $this->assertContains('scheiße', $found);
        $this->assertContains('mierda', $found);
    }
    
    public function test_removed_compound_profanities_not_detected()
    {
        // Test that removed compound profanities are not in the list
        $result = Blasp::check('fuck me hard');
        $this->assertTrue($result->hasProfanity());
        $this->assertNotContains('fuckme', $result->getUniqueProfanitiesFound());
        $this->assertNotContains('fuckmehard', $result->getUniqueProfanitiesFound());
        $this->assertNotContains('fuckher', $result->getUniqueProfanitiesFound());
        
        // But "fuck" alone should still be detected
        $this->assertContains('fuck', $result->getUniqueProfanitiesFound());
    }
    
    public function test_legitimate_compound_profanities_still_work()
    {
        // Test that legitimate compound profanities still work
        $result = Blasp::check('fuckyou you fuckhead');
        $this->assertTrue($result->hasProfanity());
        $this->assertContains('fuckyou', $result->getUniqueProfanitiesFound());
        $this->assertContains('fuckhead', $result->getUniqueProfanitiesFound());
    }
}