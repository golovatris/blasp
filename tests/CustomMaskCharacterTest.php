<?php

namespace Blaspsoft\Blasp\Tests;

use Blaspsoft\Blasp\BlaspService;

class CustomMaskCharacterTest extends TestCase
{
    protected BlaspService $blasp;

    public function setUp(): void
    {
        parent::setUp();
        $this->blasp = new BlaspService();
    }

    public function test_default_mask_character_is_asterisk()
    {
        $result = $this->blasp->check('This is fucking awesome');
        $this->assertEquals('This is ******* awesome', $result->getCleanString());
    }

    public function test_custom_mask_character_with_hash()
    {
        $result = $this->blasp->maskWith('#')->check('This is fucking awesome');
        $this->assertEquals('This is ####### awesome', $result->getCleanString());
    }

    public function test_custom_mask_character_with_dash()
    {
        $result = $this->blasp->maskWith('-')->check('This shit is bad');
        $this->assertEquals('This ---- is bad', $result->getCleanString());
    }

    public function test_custom_mask_character_with_underscore()
    {
        $result = $this->blasp->maskWith('_')->check('What the hell');
        $this->assertEquals('What the ____', $result->getCleanString());
    }

    public function test_custom_mask_character_with_unicode()
    {
        $result = $this->blasp->maskWith('●')->check('This is damn good');
        $this->assertEquals('This is ●●●● good', $result->getCleanString());
    }

    public function test_custom_mask_character_only_uses_first_character()
    {
        // If multiple characters are passed, only the first should be used
        $result = $this->blasp->maskWith('###')->check('This is fucking awesome');
        $this->assertEquals('This is ####### awesome', $result->getCleanString());
    }

    public function test_mask_character_can_be_chained_with_language()
    {
        $result = $this->blasp->spanish()->maskWith('@')->check('Esto es mierda');
        $this->assertEquals('Esto es @@@@@@', $result->getCleanString());
    }

    public function test_mask_character_works_with_multiple_profanities()
    {
        $result = $this->blasp->maskWith('!')->check('fuck this shit damn');
        $this->assertEquals('!!!! this !!!! !!!!', $result->getCleanString());
        $this->assertEquals(3, $result->getProfanitiesCount());
    }

    public function test_mask_character_persists_through_configure()
    {
        $blasp = $this->blasp->maskWith('#');
        $result = $blasp->configure(['test'], [])->check('This is a test');
        $this->assertEquals('This is a ####', $result->getCleanString());
    }

    public function test_different_mask_characters_can_be_used_independently()
    {
        $blaspHash = $this->blasp->maskWith('#');
        $blaspDash = $this->blasp->maskWith('-');
        
        $resultHash = $blaspHash->check('This is shit');
        $resultDash = $blaspDash->check('This is shit');
        
        $this->assertEquals('This is ####', $resultHash->getCleanString());
        $this->assertEquals('This is ----', $resultDash->getCleanString());
    }
}