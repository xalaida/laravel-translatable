# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]
### Removed
- The `auto archiving translations` feature.

## [0.6.0] - 2021-01-14
### Added
- Install UUID package
- Feature to archived translations
- Feature auto-archiving previous translations

### Changed
- Rename `TranslationNotFoundEvent` into `TranslationNotFound`
- Rename `TranslationSavedEvent` into `TranslationCreated`
- Make 'locale' field nullable

## [0.5.0] - 2021-01-13
### Added
- Laravel 8 support

## [0.4.0] - 2021-01-06
### Changed
- Rename method `getDefaultAttribute` into `getDefaultTranslation`

### Fixed
- Refactor tests
- Fix case when JSON serializing makes additional DB calls for translations

## [0.3.0] - 2020-07-04
### Added
- Added tests for morph map 
- Added `getTranslationOrDefault` method

### Changed
- No longer need to publish default migration
- No longer need to copy translatable attributes into the model's fillable array

### Fixed
- `getTranslation` method now returns default value if translation is missing
- `getRawTranslation` now fires event `TranslationNotFound`

## [0.2.0] - 2020-05-28
### Fixed
- Fixed primary key in the translations table

## [0.1.0] - 2020-05-16
### Added
- Everything
