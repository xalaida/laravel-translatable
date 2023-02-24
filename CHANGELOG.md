# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## 0.8.1 - 2023-02-24

### Added

- Support Laravel 10

## [Unreleased]

### Added

## [0.8.0] - 2022-06-22

### Added

- Different strategies to handle translations in their own way.

### Changed

- Project structure.
- Move trait `Nevadskiy\Translatable\HasTranslations` to `Nevadskiy\Translatable\Strategies\SingleTable\HasTranslations`.

### Removed

- `translate` and `translateMany` methods on the model.
- Archiving translations feature.
- `translations:remove-unused` command.

## [0.7.4] - 2021-11-29

### Changed

- Update uuid package.

## [0.7.3] - 2021-03-25

### Added

- Possibility to disable auto-loading translations.
- Possibility to disable auto-saving translations.

### Changed

- A bit refactoring.
- Documentation.

### Fixed

- Soft delete detection for inherited models.

## [0.7.2] - 2021-03-19

### Fixed

- Ordering by multiple translations.

## [0.7.1] - 2021-03-18

### Added

- Disabling migrations.
- Order by translatable.

## [0.7.0] - 2021-02-26

### Removed

- The `auto archiving translations` feature.

## [0.6.0] - 2021-01-14

### Added

- Install UUID package.
- Feature to archived translations.
- Feature auto-archiving previous translations.

### Changed

- Rename `TranslationNotFoundEvent` into `TranslationNotFound`.
- Rename `TranslationSavedEvent` into `TranslationCreated`.
- Make 'locale' field nullable.

## [0.5.0] - 2021-01-13

### Added

- Laravel 8 support.

## [0.4.0] - 2021-01-06

### Changed

- Rename method `getDefaultAttribute` into `getDefaultTranslation`.

### Fixed

- Refactor tests.
- Fix case when JSON serializing makes additional DB calls for translations.

## [0.3.0] - 2020-07-04

### Added

- Added tests for morph map.
- Added `getTranslationOrDefault` method.

### Changed

- No longer need to publish default migration.
- No longer need to copy translatable attributes into the model's fillable array.

### Fixed

- `getTranslation` method now returns default value if translation is missing.
- `getRawTranslation` now fires event `TranslationNotFound`.

## [0.2.0] - 2020-05-28

### Fixed

- Fixed primary key in the translations table.

## [0.1.0] - 2020-05-16

### Added

- Everything.
