# Changelog

All notable changes to `blasp` will be documented in this file

## 3.0.0 - 2025-01-05

### Added
- Custom mask character support with `maskWith()` method
- Simplified API with Laravel facade pattern and method chaining
- Comprehensive multi-language support (Spanish, German, French)
- Expanded test coverage across all languages
- Comprehensive extensibility system with full test coverage
- Basic registry pattern for language normalizers
- Language files publishing to ServiceProvider
- Comprehensive documentation for maskWith() and all chainable methods

### Changed
- Implemented dependency injection and simplified service dependencies
- Extracted expression generation logic to dedicated generator
- Improved substitution detection across all languages
- Updated README with simplified chainable API documentation
- Updated README with comprehensive multi-language support documentation
- Updated README with language files publishing options
- Updated README for v3.0 features

### Fixed
- Resolved language switching not loading correct profanities
- Prevented cross-word-boundary profanity matches

### Removed
- Strategy factory, plugin manager, and default detection strategy
- Domain-specific detection strategies (email, URL, phone)
- Unused strict() and lenient() detection modes
- README duplications and outdated references

## 1.0.0 - 201X-XX-XX

- initial release
