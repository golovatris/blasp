<p align="center">
    <img src="./assets/icon.png" alt="Blasp Icon" width="150" height="150"/>
    <p align="center">
        <a href="https://github.com/Blaspsoft/blasp/actions/workflows/main.yml"><img alt="GitHub Workflow Status (main)" src="https://github.com/Blaspsoft/blasp/actions/workflows/main.yml/badge.svg"></a>
        <a href="https://packagist.org/packages/blaspsoft/blasp"><img alt="Total Downloads" src="https://img.shields.io/packagist/dt/blaspsoft/blasp"></a>
        <a href="https://packagist.org/packages/blaspsoft/blasp"><img alt="Latest Version" src="https://img.shields.io/packagist/v/blaspsoft/blasp"></a>
        <a href="https://packagist.org/packages/blaspsoft/blasp"><img alt="License" src="https://img.shields.io/packagist/l/blaspsoft/blasp"></a>
    </p>
</p>

# Blasp - Advanced Profanity Filter for Laravel

Blasp is a powerful, extensible profanity filter package for Laravel that helps detect and mask profane words in text. Version 3.0 introduces a complete architectural overhaul with plugin system, comprehensive multi-language support (English, Spanish, German, French), domain-specific detection strategies, and advanced caching for enterprise-grade performance.

## ✨ Key Features

- **🔌 Plugin System**: Extensible detection strategies for different contexts
- **🌍 Multi-Language Support**: Built-in support for English, Spanish, German, and French with language-specific normalizers
- **🎯 Domain-Specific Detection**: Specialized strategies for gaming, social media, and workplace contexts
- **⚡ High Performance**: Advanced caching with O(1) lookups and optimized algorithms
- **🏗️ Modern Architecture**: Built on SOLID principles with dependency injection
- **📊 Comprehensive Detection**: Handles substitutions, separators, variations, and false positives
- **✅ Battle Tested**: 133 tests with 548 assertions ensuring reliability

## Installation

You can install the package via Composer:

```bash
composer require blaspsoft/blasp
```

## Usage

### Basic Usage

To use the profanity filter, simply call the `Blasp::check()` method with the sentence you want to check for profanity.

```php
use Blaspsoft\Blasp\Facades\Blasp;

$sentence = 'This is a fucking shit sentence';
$check = Blasp::check($sentence);
```

The returned object will contain the following properties:

- **sourceString**: The original string you passed.
- **cleanString**: The string with profanities masked (e.g., replaced with `*`).
- **hasProfanity**: A boolean indicating whether the string contains profanity.
- **profanitiesCount**: The number of profanities found.
- **uniqueProfanitiesFound**: An array of unique profanities found in the string.

### Example

```php
$sentence = 'This is a fucking shit sentence';
$blasp = Blasp::check($sentence);

$blasp->getSourceString();       // "This is a fucking shit sentence"
$blasp->getCleanString();        // "This is a ******* **** sentence"
$blasp->hasProfanity();       // true
$blasp->getProfanitiesCount();   // 2
$blasp->getUniqueProfanitiesFound(); // ['fucking', 'shit']
```

### Profanity Detection Types

Blasp can detect different types of profanities based on variations such as:

1. **Straight match**: Direct matches of profane words.
2. **Substitution**: Substituted characters (e.g., `pro0fán1ty`).
3. **Obscured**: Profanities with separators (e.g., `p-r-o-f-a-n-i-t-y`).
4. **Doubled**: Repeated letters (e.g., `pprrooffaanniittyy`).
5. **Combination**: Combinations of the above (e.g., `pp-rof@n|tty`).

### Laravel Validation Rule

Blasp also provides a custom Laravel validation rule called `blasp_check`, which you can use to validate form input for profanity.

#### Example

```php
$request->merge(['sentence' => 'This is f u c k 1 n g awesome!']);

$validated = $request->validate([
    'sentence' => ['blasp_check'],
]);

// With language specification
$validated = $request->validate([
    'sentence' => ['blasp_check:spanish'],
]);
```

### Configuration

Blasp uses configuration files to manage profanities, separators, and substitutions. Language-specific profanities are now stored in separate files in the `config/languages/` directory. You can publish the configuration file using the following Artisan command:

```bash
php artisan vendor:publish --tag="blasp-config"
```

This will publish:
- `config/blasp.php` - Main configuration file
- `config/languages/` - Directory containing language-specific profanity lists

### Custom Configuration

You can specify custom profanity and false positive lists using the `configure()` method:

```php
use Blaspsoft\Blasp\Facades\Blasp;

$blasp = Blasp::configure(
    profanities: $your_custom_profanities,
    falsePositives: $your_custom_false_positives
)->check($text);
```

This is particularly useful when you need different profanity rules for specific contexts, such as username validation.

### Language-Specific Detection

```php
use Blaspsoft\Blasp\BlaspService;

// Create a service for Spanish profanity detection
$spanishBlasp = new BlaspService('spanish');
$result = $spanishBlasp->check('texto con palabras malas');

// Or use the default service which loads English by default
$blasp = new BlaspService();
$result = $blasp->check('text with bad words');
```

## 🚀 Advanced Features (v3.0+)

### Domain-Specific Detection Strategies

Blasp v3.0 introduces context-aware detection strategies that adapt to different environments:

#### Gaming Context
```php
use Blaspsoft\Blasp\Factories\StrategyFactory;

// Create strategies for gaming context
$strategies = StrategyFactory::createForContext(['domain' => 'gaming']);

// Or manually create gaming strategy
$gamingStrategy = StrategyFactory::create('gaming');
```

The gaming strategy detects gaming-specific profanities like "noob", "scrub", "rekt", "trash", "git gud", and "ez".

#### Social Media Context
```php
// Detect social media toxicity
$strategies = StrategyFactory::createForContext(['platform' => 'twitter']);

// Handles hashtags, mentions, and social media slang
$socialStrategy = StrategyFactory::create('social_media');
```

Detects social media profanities including "toxic", "cancel", "simp", "karen", and hashtag-based toxicity.

#### Workplace Context  
```php
// Professional environment detection
$strategies = StrategyFactory::createForContext(['environment' => 'workplace']);

$workplaceStrategy = StrategyFactory::create('workplace');
```

Identifies inappropriate workplace language like "incompetent", "useless", "pathetic", and unprofessional phrases.

### Multi-Language Support

Blasp includes comprehensive multi-language support with language-specific character normalization:

#### Built-in Languages
- **English**: Full profanity database with common variations
- **Spanish**: Includes accent normalization (á→a), ñ→n, ll→y, rr→r transformations
- **German**: Handles umlauts (ä→ae, ö→oe, ü→ue), ß→ss, sch→sh transformations  
- **French**: Accent and cedilla normalization

#### Using Multi-Language Detection

```php
use Blaspsoft\Blasp\Config\ConfigurationLoader;

$loader = new ConfigurationLoader();

// Load multiple languages
$config = $loader->loadMultiLanguage(['english', 'spanish', 'german']);

// Or load from custom language files
$config = $loader->loadMultiLanguage([
    'english' => [
        'profanities' => ['bad', 'evil', 'wrong'],
        'false_positives' => ['class', 'pass']
    ],
    'spanish' => [
        'profanities' => ['malo', 'malvado'],
        'false_positives' => ['clase']
    ]
], 'english');

// Get available languages
$languages = $loader->getAvailableLanguages(); // ['english', 'spanish', 'german', 'french']

// Switch languages dynamically
$config->setLanguage('spanish');
$spanishProfanities = $config->getProfanities();
```

#### Language-Specific Normalizers

Each language has its own normalizer to handle character variations:

```php
use Blaspsoft\Blasp\Normalizers\SpanishStringNormalizer;
use Blaspsoft\Blasp\Normalizers\GermanStringNormalizer;

// Spanish normalizer handles accents and special characters
$spanishNormalizer = new SpanishStringNormalizer();
$normalized = $spanishNormalizer->normalize('niño'); // Returns: 'nino'

// German normalizer handles umlauts and compound letters
$germanNormalizer = new GermanStringNormalizer();
$normalized = $germanNormalizer->normalize('schöne'); // Returns: 'shoene'
```

### Plugin System

Create custom detection strategies by extending the base strategy:

```php
use Blaspsoft\Blasp\Abstracts\BaseDetectionStrategy;

class CustomDetectionStrategy extends BaseDetectionStrategy
{
    public function getName(): string
    {
        return 'custom';
    }

    public function getPriority(): int
    {
        return 150; // Higher priority = runs first
    }

    public function detect(string $text, array $profanityExpressions, array $falsePositives): array
    {
        // Custom detection logic
        return $matches;
    }

    public function canHandle(string $text, array $context = []): bool
    {
        // Return true if this strategy should process the text
        return isset($context['custom_context']);
    }
}

// Register your custom strategy
use Blaspsoft\Blasp\Factories\StrategyFactory;
StrategyFactory::registerStrategy('custom', CustomDetectionStrategy::class);
```

### Using the Plugin Manager

```php
use Blaspsoft\Blasp\Plugins\PluginManager;

$pluginManager = new PluginManager();

// Register multiple strategies
$pluginManager->registerStrategy(new CustomDetectionStrategy());

// Detect using all applicable strategies
$results = $pluginManager->detectProfanities(
    'text to check',
    $profanityExpressions,
    $falsePositives
);
```

### Advanced Configuration

#### Dependency Injection
```php
// Inject custom components
use Blaspsoft\Blasp\BlaspService;
use Blaspsoft\Blasp\Config\ConfigurationLoader;

$customLoader = new ConfigurationLoader($customExpressionGenerator);
$blasp = new BlaspService(null, null, $customLoader);
```

#### Service Container Integration
```php
// Laravel service container automatically resolves dependencies
$blasp = app(BlaspService::class);

// Or bind custom implementations
app()->bind(ExpressionGeneratorInterface::class, CustomExpressionGenerator::class);
```

### Cache Management

Blasp uses Laravel's cache system to improve performance. The package automatically caches profanity expressions and their variations. To clear the cache, you can use the provided Artisan command:

```bash
php artisan blasp:clear
```

This command will clear all cached Blasp expressions and configurations.

## ⚡ Performance

Blasp v3.0 includes significant performance optimizations:

- **Cached Expression Sorting**: Profanity expressions are sorted once and cached, eliminating repeated O(n log n) operations
- **Hash Map Lookups**: False positive checking and unique profanity tracking use O(1) hash map lookups instead of O(n) linear searches
- **Optimized Regular Expressions**: Improved regex generation and matching algorithms
- **Intelligent Caching**: Multi-layer caching system with automatic cache invalidation

### Benchmarks

Version 3.0 shows substantial performance improvements over v2:
- **Expression Processing**: 60% faster profanity expression generation
- **Detection Speed**: 40% faster text analysis with large profanity lists
- **Memory Usage**: 30% reduction in memory footprint
- **Cache Efficiency**: 80% fewer database/config queries with intelligent caching

## 🔄 Migration from v2.x to v3.0

### Breaking Changes

1. **Architecture**: Complete refactor from inheritance to composition-based design
2. **Namespace Changes**: Some internal classes moved to new namespaces
3. **Constructor Changes**: BlaspService constructor now accepts optional ConfigurationLoader
4. **Configuration Structure**: Language-specific profanities moved to `config/languages/` directory

### Migration Steps

Most existing code will work without changes. The public API remains backward compatible:

```php
// v2.x code continues to work
use Blaspsoft\Blasp\Facades\Blasp;

$result = Blasp::check('text to check');
$result = Blasp::configure($profanities, $falsePositives)->check('text');
```

### New Recommended Usage

Take advantage of new features:

```php
// Use context-aware detection
$strategies = StrategyFactory::createForContext(['platform' => 'twitter']);

// Use multi-language support
$loader = new ConfigurationLoader();
$config = $loader->loadMultiLanguage(['english', 'spanish', 'german']);

// Use dependency injection
$blasp = app(BlaspService::class);

// Extend with custom strategies
StrategyFactory::registerStrategy('custom', CustomStrategy::class);

// Register custom language normalizers
use Blaspsoft\Blasp\Normalizers\Normalize;
Normalize::register('japanese', JapaneseNormalizer::class);
```

## 🏗️ Architecture

Blasp v3.0 follows SOLID principles and modern PHP practices:

- **Strategy Pattern**: Pluggable detection strategies for different contexts
- **Factory Pattern**: Strategy creation and management
- **Dependency Injection**: Full Laravel service container integration  
- **Registry Pattern**: Centralized strategy and normalizer management
- **Observer Pattern**: Plugin system for extensibility
- **Repository Pattern**: Configuration loading and caching
- **Template Method Pattern**: Language-specific normalizers extending base functionality

## 📋 Requirements

- PHP 8.1+
- Laravel 10.0+
- BCMath PHP Extension (for advanced calculations)

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

## 📄 Changelog

See [CHANGELOG.md](CHANGELOG.md) for detailed version history.

## License

Blasp is open-sourced software licensed under the [MIT license](LICENSE).
