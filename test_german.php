<?php

require_once 'vendor/autoload.php';

use Blaspsoft\Blasp\BlaspService;

// Test all languages with proper configuration
$languages = ['english', 'german', 'french', 'spanish'];

$testPhrases = [
    'english' => "You are a fucking cunt",
    'german' => "Du bist eine verdammte Fotze",
    'french' => "Tu es un putain de connard",
    'spanish' => "Eres un maldito hijo de puta"
];

echo "=== BLASP Multi-Language Profanity Detection Demo ===\n\n";

foreach ($languages as $language) {
    echo "Testing $language:\n";
    echo "─────────────────\n";
    
    // Load the language-specific configuration
    $languageConfig = require __DIR__ . "/config/languages/$language.php";
    
    // Create BlaspService with language-specific profanities
    $blasp = new BlaspService(
        $languageConfig['profanities'],
        $languageConfig['false_positives'] ?? []
    );
    
    $text = $testPhrases[$language];
    $result = $blasp->check($text);
    
    echo "Original: $text\n";
    echo "Censored: {$result->cleanString}\n";
    echo "Has Profanity: " . ($result->hasProfanity ? 'Yes' : 'No') . "\n";
    echo "Count: {$result->profanitiesCount}\n";
    echo "Found: " . implode(', ', $result->uniqueProfanitiesFound) . "\n\n";
}

echo "Note: To use language-specific detection, you need to:\n";
echo "1. Load the language configuration from config/languages/{language}.php\n";
echo "2. Create BlaspService with that language's profanities and false positives\n";
echo "3. Then use ->check() method as usual\n";