# Laravel Translatable

[![Tests](https://github.com/nevadskiy/laravel-translatable/workflows/Tests/badge.svg)](https://packagist.org/packages/nevadskiy/laravel-translatable)
[![Code Coverage](https://codecov.io/gh/nevadskiy/laravel-translatable/branch/master/graphs/badge.svg?branch=master)](https://packagist.org/packages/nevadskiy/laravel-translatable)
[![License](https://poser.pugx.org/nevadskiy/laravel-translatable/license)](https://packagist.org/packages/nevadskiy/laravel-translatable)
[![Latest Stable Version](https://poser.pugx.org/nevadskiy/laravel-translatable/v)](https://packagist.org/packages/nevadskiy/laravel-translatable)

The package allows adding translations for your Eloquent models.

## 🍬 Features

- Translatable attributes behave like regular model attributes.
- Full support for accessors, mutators and casts (even JSON).
- Fallback translations.
- 4 different strategies for storing translations.

## 📺 Quick demo

```php
$book = new Book()
$book->translator()->set('title', 'Fifty miles', 'en')
$book->translator()->set('title', "П'ятдесят верстов", 'uk')
$book->save();

app()->setLocale('en');
echo $book->title; // Fifty miles

app()->setLocale('uk');
echo $book->title; // П'ятдесят верстов
```

## ✅ Requirements

- PHP `7.2` or newer
- Laravel `7.0` or newer  
- Can work with [Octane](https://github.com/laravel/octane)

## 🔌 Installation

Install the package via composer:

```bash
composer require nevadskiy/laravel-translatable
```

## 📄 Documentation

Documentation for the package can be found in the [Wiki section](https://github.com/nevadskiy/laravel-translatable/wiki). 

## ✨ Laravel Nova

There is a [small package](https://github.com/nevadskiy/nova-translatable) for the Laravel Nova admin that adds support for translatable fields.

## 📑 Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## ☕ Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for more information.

## 🔓 Security

If you discover any security related issues, please [e-mail me](mailto:nevadskiy@gmail.com) instead of using the issue tracker.

## 📜 License

The MIT License (MIT). Please see [LICENSE](LICENSE.md) for more information.
