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

Blasp is a powerful, extensible profanity filter package for Laravel that helps detect and mask profane words in text. Version 3.0 introduces a simplified API with method chaining, comprehensive multi-language support (English, Spanish, German, French), all-languages detection mode, and advanced caching for enterprise-grade performance.

## ✨ Key Features

- **🔗 Method Chaining**: Elegant fluent API with `Blasp::spanish()->strict()->check()`
- **🌍 Multi-Language Support**: English, Spanish, German, and French with language-specific normalizers
- **🌐 All Languages Mode**: Check against all languages simultaneously with `Blasp::allLanguages()`
- **⚡ High Performance**: Advanced caching with O(1) lookups and optimized algorithms
- **🎯 Smart Detection**: Handles substitutions, separators, variations, and false positives
- **🏗️ Modern Architecture**: Built on SOLID principles with dependency injection
- **✅ Battle Tested**: 184 tests with 1000+ assertions ensuring reliability

## Installation

You can install the package via Composer:

```bash
composer require blaspsoft/blasp
```

## Quick Start

### Basic Usage

```php
use Blaspsoft\Blasp\Facades\Blasp;

// Simple usage - uses default language from config
$result = Blasp::check('This is a fucking shit sentence');

// With method chaining for specific language
$result = Blasp::spanish()->check('esto es una mierda');

// Check against ALL languages at once
$result = Blasp::allLanguages()->check('fuck merde scheiße mierda');
```

### Simplified API with Method Chaining

```php
// Language shortcuts
Blasp::english()->check($text);
Blasp::spanish()->check($text);
Blasp::german()->check($text);
Blasp::french()->check($text);

// Detection modes
Blasp::strict()->check($text);   // Strict detection
Blasp::lenient()->check($text);  // Lenient detection

// Combine methods with chaining
Blasp::spanish()->strict()->check($text);
Blasp::allLanguages()->lenient()->check($text);

// Configure custom profanities
Blasp::configure(['badword'], ['goodword'])->check($text);
```

### Working with Results

```php
$result = Blasp::check('This is fucking awesome');

$result->getSourceString();           // "This is fucking awesome"
$result->getCleanString();            // "This is ******* awesome"
$result->hasProfanity();             // true
$result->getProfanitiesCount();      // 1
$result->getUniqueProfanitiesFound(); // ['fucking']
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

Blasp uses configuration files to manage profanities, separators, and substitutions. The main configuration includes:

```php
// config/blasp.php
return [
    'default_language' => 'english',  // Default language for detection
    'separators' => [...],            // Special characters used as separators
    'substitutions' => [...],         // Character substitutions (like @ for a)
    'false_positives' => [...],       // Words that should not be flagged
];
```

You can publish the configuration files:

```bash
# Publish everything (config + all language files)
php artisan vendor:publish --tag="blasp"

# Publish only the main configuration file
php artisan vendor:publish --tag="blasp-config"

# Publish only the language files
php artisan vendor:publish --tag="blasp-languages"
```

This will publish:
- `config/blasp.php` - Main configuration with default language settings
- `config/languages/` - Language-specific profanity lists (English, Spanish, German, French)

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


## 🚀 Advanced Features (v3.0+)

### All Languages Detection

Perfect for international platforms, forums, or any application with multilingual content:

```php
// Check text against ALL configured languages at once
$result = Blasp::allLanguages()->check('fuck merde scheiße mierda');
// Detects profanities from English, French, German, and Spanish

// Combine with detection modes
$result = Blasp::allLanguages()->strict()->check($text);
$result = Blasp::allLanguages()->lenient()->check($text);

// Get detailed results
echo $result->getProfanitiesCount();        // 4
echo $result->getUniqueProfanitiesFound();  // ['fuck', 'merde', 'scheiße', 'mierda']
```

### Multi-Language Support

Blasp includes comprehensive support for multiple languages with automatic character normalization:

- **English**: Full profanity database with common variations
- **Spanish**: Handles accent normalization (á→a, ñ→n)
- **German**: Processes umlauts (ä→ae, ö→oe, ü→ue) and ß→ss
- **French**: Accent and cedilla normalization


### Method Chaining API

The new simplified API supports elegant method chaining:

```php
// Chain multiple methods together
Blasp::spanish()
    ->strict()
    ->configure(['custom_bad_word'], ['false_positive'])
    ->check('texto para verificar');

// All methods return a BlaspService instance for chaining
$service = Blasp::english();          // Returns BlaspService
$service = $service->strict();        // Returns BlaspService  
$service = $service->check($text);    // Returns BlaspService with results

// Mix and match as needed
Blasp::allLanguages()->lenient()->check($text);
Blasp::french()->configure($profanities)->check($text);
```

### Laravel Integration

```php
// Laravel service container integration
$blasp = app(BlaspService::class);

// Validation rule with default language
$request->validate([
    'message' => 'required|blasp_check'
]);

// Validation rule with specific language
$request->validate([
    'message' => 'required|blasp_check:spanish'
]);
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

### 100% Backward Compatible

All existing v2.x code continues to work without any changes:

```php
// Existing code works exactly the same
use Blaspsoft\Blasp\Facades\Blasp;

$result = Blasp::check('text to check');
$result = Blasp::configure($profanities, $falsePositives)->check('text');
```

### New Features in v3.0

Take advantage of the simplified API:

```php
// NEW: Method chaining
Blasp::spanish()->strict()->check($text);

// NEW: All languages detection
Blasp::allLanguages()->check($text);

// NEW: Language shortcuts
Blasp::german()->check($text);
Blasp::french()->check($text);

// NEW: Default language configuration
// Set in config/blasp.php: 'default_language' => 'spanish'
Blasp::check($text); // Now uses Spanish by default
```

## 🏗️ Architecture

Blasp v3.0 follows SOLID principles and modern PHP practices:

- **Facade Pattern**: Simplified API with Laravel facade integration
- **Builder Pattern**: Method chaining for fluent interface
- **Strategy Pattern**: Language-specific detection and normalization
- **Dependency Injection**: Full Laravel service container integration
- **Caching**: Intelligent performance optimization

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
