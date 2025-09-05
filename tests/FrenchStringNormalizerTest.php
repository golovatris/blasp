<?php

namespace Blaspsoft\Blasp\Tests;

use Blaspsoft\Blasp\Normalizers\FrenchStringNormalizer;

class FrenchStringNormalizerTest extends TestCase
{
    private FrenchStringNormalizer $normalizer;

    public function setUp(): void
    {
        parent::setUp();
        $this->normalizer = new FrenchStringNormalizer();
    }

    public function test_normalize_accented_vowels()
    {
        // Test various French accents
        $this->assertEquals('ecole eleve', $this->normalizer->normalize('école élève'));
        $this->assertEquals('cafe the', $this->normalizer->normalize('café thé'));
        $this->assertEquals('hotel foret', $this->normalizer->normalize('hôtel forêt'));
        $this->assertEquals('ou deja', $this->normalizer->normalize('où déjà'));
        $this->assertEquals('naive Noel', $this->normalizer->normalize('naïve Noël'));
    }

    public function test_normalize_cedilla()
    {
        $this->assertEquals('francais garcon', $this->normalizer->normalize('français garçon'));
        $this->assertEquals('ca commence', $this->normalizer->normalize('ça commence'));
        $this->assertEquals('FRANCAIS', $this->normalizer->normalize('FRANÇAIS'));
    }

    public function test_normalize_ligatures()
    {
        $this->assertEquals('oeuvre coeur', $this->normalizer->normalize('œuvre cœur'));
        $this->assertEquals('soeur boeuf', $this->normalizer->normalize('sœur bœuf'));
        $this->assertEquals('OEUVRE', $this->normalizer->normalize('ŒUVRE'));
    }

    public function test_normalize_french_profanity_variants()
    {
        // Test common French profanities with accents
        $this->assertEquals('merde', $this->normalizer->normalize('mèrde'));
        $this->assertEquals('encule', $this->normalizer->normalize('enculé'));
        $this->assertEquals('connard', $this->normalizer->normalize('cônnard'));
        $this->assertEquals('putain', $this->normalizer->normalize('putàin'));
        $this->assertEquals('salope', $this->normalizer->normalize('sâlope'));
    }

    public function test_normalize_circumflex_accent()
    {
        $this->assertEquals('hopital', $this->normalizer->normalize('hôpital'));
        $this->assertEquals('tete', $this->normalizer->normalize('tête'));
        $this->assertEquals('etre', $this->normalizer->normalize('être'));
        $this->assertEquals('chateaux', $this->normalizer->normalize('châteaux'));
        $this->assertEquals('cote', $this->normalizer->normalize('côte'));
    }

    public function test_normalize_grave_accent()
    {
        $this->assertEquals('tres', $this->normalizer->normalize('très'));
        $this->assertEquals('apres', $this->normalizer->normalize('après'));
        $this->assertEquals('des', $this->normalizer->normalize('dès'));
        $this->assertEquals('premiere', $this->normalizer->normalize('première'));
        $this->assertEquals('deuxieme', $this->normalizer->normalize('deuxième'));
    }

    public function test_normalize_acute_accent()
    {
        $this->assertEquals('ete', $this->normalizer->normalize('été'));
        $this->assertEquals('ecole', $this->normalizer->normalize('école'));
        $this->assertEquals('eleve', $this->normalizer->normalize('élève'));
        $this->assertEquals('general', $this->normalizer->normalize('général'));
        $this->assertEquals('celebre', $this->normalizer->normalize('célèbre'));
    }

    public function test_normalize_diaeresis()
    {
        $this->assertEquals('naive', $this->normalizer->normalize('naïve'));
        $this->assertEquals('heroine', $this->normalizer->normalize('héroïne'));
        $this->assertEquals('mais', $this->normalizer->normalize('maïs'));
        $this->assertEquals('Noel', $this->normalizer->normalize('Noël'));
        $this->assertEquals('Israel', $this->normalizer->normalize('Israël'));
    }

    public function test_normalize_mixed_case_preservation()
    {
        $this->assertEquals('MERDE', $this->normalizer->normalize('MÈRDE'));
        $this->assertEquals('Putain', $this->normalizer->normalize('Putàin'));
        $this->assertEquals('CoNNaRD', $this->normalizer->normalize('CôNNaRD'));
        $this->assertEquals('sAlOPe', $this->normalizer->normalize('sÂlOPe'));
    }

    public function test_normalize_preserves_non_french_characters()
    {
        $this->assertEquals('hello world 123', $this->normalizer->normalize('hello world 123'));
        $this->assertEquals('test@email.com', $this->normalizer->normalize('test@email.com'));
        $this->assertEquals('user_name-123', $this->normalizer->normalize('user_name-123'));
    }

    public function test_normalize_empty_and_special_strings()
    {
        $this->assertEquals('', $this->normalizer->normalize(''));
        $this->assertEquals('   ', $this->normalizer->normalize('   '));
        $this->assertEquals('eeee', $this->normalizer->normalize('éèêë'));
        $this->assertEquals('aaaa', $this->normalizer->normalize('àâäá'));
    }

    public function test_normalize_complex_french_text()
    {
        $input = "L'école française où les élèves étudient l'œuvre de Molière";
        $expected = "L'ecole francaise ou les eleves etudient l'oeuvre de Moliere";
        $this->assertEquals($expected, $this->normalizer->normalize($input));
    }

    public function test_normalize_all_french_accents()
    {
        // Comprehensive test of all French accented characters
        $accents = [
            'à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
            'ç' => 'c',
            'œ' => 'oe', 'æ' => 'ae',
            // Uppercase
            'À' => 'A', 'Â' => 'A', 'Ä' => 'A', 'Á' => 'A',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Ö' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ý' => 'Y', 'Ÿ' => 'Y',
            'Ç' => 'C',
            'Œ' => 'OE', 'Æ' => 'AE'
        ];

        foreach ($accents as $accented => $normalized) {
            $this->assertEquals(
                $normalized,
                $this->normalizer->normalize($accented),
                "Failed to normalize '$accented' to '$normalized'"
            );
        }
    }

    public function test_normalize_numbers_and_special_chars()
    {
        // Test that numbers and special characters are preserved
        $this->assertEquals('123abc', $this->normalizer->normalize('123abc'));
        $this->assertEquals('test!@#$%', $this->normalizer->normalize('test!@#$%'));
        $this->assertEquals('hello_world-2024', $this->normalizer->normalize('hello_world-2024'));
    }

    public function test_normalize_french_profanities_from_config()
    {
        $config = require __DIR__ . '/../config/languages/french.php';
        $profanities = array_slice($config['profanities'], 0, 20); // Test subset
        
        foreach ($profanities as $profanity) {
            $normalized = $this->normalizer->normalize($profanity);
            
            // The normalized version should not contain any French special characters
            $this->assertDoesNotMatchRegularExpression(
                '/[àâäáèéêëìíîïòóôöùúûüýÿçœæÀÂÄÁÈÉÊËÌÍÎÏÒÓÔÖÙÚÛÜÝŸÇŒÆ]/',
                $normalized,
                "French profanity '$profanity' still contains accents after normalization: '$normalized'"
            );
        }
    }
}