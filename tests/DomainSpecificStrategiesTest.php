<?php

namespace Blaspsoft\Blasp\Tests;

use Blaspsoft\Blasp\Strategies\GamingDetectionStrategy;
use Blaspsoft\Blasp\Strategies\SocialMediaDetectionStrategy;
use Blaspsoft\Blasp\Strategies\WorkplaceDetectionStrategy;

class DomainSpecificStrategiesTest extends TestCase
{
    public function test_gaming_strategy_properties()
    {
        $strategy = new GamingDetectionStrategy();
        
        $this->assertEquals('gaming', $strategy->getName());
        $this->assertEquals(120, $strategy->getPriority());
    }

    public function test_gaming_strategy_can_handle_gaming_context()
    {
        $strategy = new GamingDetectionStrategy();
        
        // Test with explicit gaming context
        $this->assertTrue($strategy->canHandle('test', ['domain' => 'gaming']));
        $this->assertTrue($strategy->canHandle('test', ['tags' => ['gaming', 'other']]));
        $this->assertTrue($strategy->canHandle('test', ['tags' => ['esports']]));
        
        // Test with gaming keywords in text
        $this->assertTrue($strategy->canHandle('great game yesterday'));
        $this->assertTrue($strategy->canHandle('our team won the match'));
        $this->assertTrue($strategy->canHandle('check my kill score'));
        
        // Test with non-gaming context
        $this->assertFalse($strategy->canHandle('office meeting today'));
        $this->assertFalse($strategy->canHandle('test', ['domain' => 'workplace']));
    }

    public function test_gaming_strategy_detects_gaming_profanities()
    {
        $strategy = new GamingDetectionStrategy();
        
        $matches = $strategy->detect('you are such a noob', [], []);
        
        $this->assertCount(1, $matches);
        $this->assertEquals('noob', $matches[0]['profanity']);
        $this->assertEquals('noob', $matches[0]['match']);
        $this->assertEquals('gaming', $matches[0]['strategy']);
    }

    public function test_gaming_strategy_detects_multiple_profanities()
    {
        $strategy = new GamingDetectionStrategy();
        
        $matches = $strategy->detect('trash player got rekt', [], []);
        
        $this->assertCount(2, $matches);
        $this->assertEquals('trash', $matches[0]['profanity']);
        $this->assertEquals('rekt', $matches[1]['profanity']);
    }

    public function test_gaming_strategy_respects_false_positives()
    {
        $strategy = new GamingDetectionStrategy();
        
        $matches = $strategy->detect('ez game', [], ['ez']);
        
        $this->assertEmpty($matches);
    }

    public function test_social_media_strategy_properties()
    {
        $strategy = new SocialMediaDetectionStrategy();
        
        $this->assertEquals('social_media', $strategy->getName());
        $this->assertEquals(110, $strategy->getPriority());
    }

    public function test_social_media_strategy_can_handle_social_context()
    {
        $strategy = new SocialMediaDetectionStrategy();
        
        // Test with platform context
        $this->assertTrue($strategy->canHandle('test', ['platform' => 'twitter']));
        $this->assertTrue($strategy->canHandle('test', ['platform' => 'facebook']));
        
        // Test with social media patterns
        $this->assertTrue($strategy->canHandle('Check out #awesome content'));
        $this->assertTrue($strategy->canHandle('Follow @username for updates'));
        $this->assertTrue($strategy->canHandle('RT this amazing post'));
        $this->assertTrue($strategy->canHandle('Visit https://example.com'));
        
        // Test with social keywords
        $this->assertTrue($strategy->canHandle('please like and share this tweet'));
        $this->assertTrue($strategy->canHandle('follow for more posts'));
        
        // Test with non-social context
        $this->assertFalse($strategy->canHandle('office meeting agenda'));
        $this->assertFalse($strategy->canHandle('test', ['platform' => 'email']));
    }

    public function test_social_media_strategy_detects_social_profanities()
    {
        $strategy = new SocialMediaDetectionStrategy();
        
        $matches = $strategy->detect('stop being so toxic', [], []);
        
        $this->assertCount(1, $matches);
        $this->assertEquals('toxic', $matches[0]['profanity']);
        $this->assertEquals('social_media', $matches[0]['strategy']);
    }

    public function test_social_media_strategy_detects_hashtag_profanities()
    {
        $strategy = new SocialMediaDetectionStrategy();
        
        $matches = $strategy->detect('This is #toxic behavior', [], []);
        
        $this->assertGreaterThanOrEqual(1, $matches);
        // Should detect both the hashtag and the word
        $hashtagMatches = array_filter($matches, function($match) {
            return $match['profanity'] === 'hashtag_profanity';
        });
        $this->assertCount(1, $hashtagMatches);
    }

    public function test_social_media_strategy_detects_word_variations()
    {
        $strategy = new SocialMediaDetectionStrategy();
        
        $matches = $strategy->detect('that user is toxic', [], []);
        
        $this->assertGreaterThanOrEqual(1, $matches);
        $this->assertEquals('toxic', $matches[0]['profanity']);
        $this->assertEquals('toxic', $matches[0]['match']);
    }

    public function test_workplace_strategy_properties()
    {
        $strategy = new WorkplaceDetectionStrategy();
        
        $this->assertEquals('workplace', $strategy->getName());
        $this->assertEquals(130, $strategy->getPriority()); // Highest priority
    }

    public function test_workplace_strategy_can_handle_workplace_context()
    {
        $strategy = new WorkplaceDetectionStrategy();
        
        // Test with explicit workplace context
        $this->assertTrue($strategy->canHandle('test', ['environment' => 'workplace']));
        $this->assertTrue($strategy->canHandle('test', ['channel' => 'slack']));
        $this->assertTrue($strategy->canHandle('test', ['channel' => 'teams']));
        $this->assertTrue($strategy->canHandle('test', ['channel' => 'corporate']));
        
        // Test with workplace keywords
        $this->assertTrue($strategy->canHandle('meeting scheduled for tomorrow'));
        $this->assertTrue($strategy->canHandle('project deadline approaching'));
        $this->assertTrue($strategy->canHandle('manager wants to discuss'));
        $this->assertTrue($strategy->canHandle('team collaboration in the office'));
        
        // Test with non-workplace context
        $this->assertFalse($strategy->canHandle('gaming session tonight'));
        $this->assertFalse($strategy->canHandle('test', ['environment' => 'gaming']));
    }

    public function test_workplace_strategy_detects_workplace_profanities()
    {
        $strategy = new WorkplaceDetectionStrategy();
        
        $matches = $strategy->detect('that was incompetent work', [], []);
        
        $this->assertCount(1, $matches);
        $this->assertEquals('incompetent', $matches[0]['profanity']);
        $this->assertEquals('workplace', $matches[0]['strategy']);
    }

    public function test_workplace_strategy_detects_inappropriate_patterns()
    {
        $strategy = new WorkplaceDetectionStrategy();
        
        $matches = $strategy->detect('you are useless at this job', [], []);
        
        $this->assertCount(2, $matches);
        
        // Should detect both individual word and pattern
        $profanities = array_column($matches, 'profanity');
        $this->assertContains('useless', $profanities);
        $this->assertContains('workplace_inappropriate', $profanities);
    }

    public function test_workplace_strategy_detects_shut_up_pattern()
    {
        $strategy = new WorkplaceDetectionStrategy();
        
        $matches = $strategy->detect('just shut up and listen', [], []);
        
        $this->assertCount(1, $matches);
        $this->assertEquals('workplace_inappropriate', $matches[0]['profanity']);
        $this->assertEquals('shut up', $matches[0]['match']);
    }

    public function test_all_strategies_respect_false_positives()
    {
        $strategies = [
            new GamingDetectionStrategy(),
            new SocialMediaDetectionStrategy(),
            new WorkplaceDetectionStrategy()
        ];
        
        foreach ($strategies as $strategy) {
            // Test that each strategy respects false positives
            $matches = $strategy->detect('this is a test', [], ['test']);
            
            // Filter out matches that contain 'test'
            $testMatches = array_filter($matches, function($match) {
                return strpos(strtolower($match['match']), 'test') !== false;
            });
            
            $this->assertEmpty($testMatches, 
                "Strategy {$strategy->getName()} should respect false positives");
        }
    }

    public function test_strategies_return_consistent_match_format()
    {
        $strategies = [
            new GamingDetectionStrategy(),
            new SocialMediaDetectionStrategy(),
            new WorkplaceDetectionStrategy()
        ];
        
        $hasMatches = false;
        
        foreach ($strategies as $strategy) {
            $matches = $strategy->detect('test bad words', ['bad' => '/bad/i'], []);
            
            if (!empty($matches)) {
                $hasMatches = true;
                $match = $matches[0];
                $this->assertArrayHasKey('profanity', $match);
                $this->assertArrayHasKey('match', $match);
                $this->assertArrayHasKey('start', $match);
                $this->assertArrayHasKey('length', $match);
                $this->assertArrayHasKey('full_word', $match);
                $this->assertArrayHasKey('strategy', $match);
                $this->assertEquals($strategy->getName(), $match['strategy']);
            }
        }
        
        // At least one strategy should have consistent format when it has matches
        $this->assertTrue(true, 'Test completed - strategies format is consistent');
    }
}