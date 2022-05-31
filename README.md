# Laravel Translatable

[![Tests](https://github.com/nevadskiy/laravel-translatable/workflows/Tests/badge.svg)](https://packagist.org/packages/nevadskiy/laravel-translatable)
[![Code Coverage](https://codecov.io/gh/nevadskiy/laravel-translatable/branch/master/graphs/badge.svg?branch=master)](https://packagist.org/packages/nevadskiy/laravel-translatable)
[![License](https://poser.pugx.org/nevadskiy/laravel-translatable/license)](https://packagist.org/packages/nevadskiy/laravel-translatable)
[![Latest Stable Version](https://poser.pugx.org/nevadskiy/laravel-translatable/v)](https://packagist.org/packages/nevadskiy/laravel-translatable)

The package allows adding translations for your Eloquent models.

## 🍬 Features 

- Storing model translations in the database.
- Support model accessors & mutators & casts (even JSON).
- Auto-resolving model translations for the current locale.
- No need to rewrite existing code to make a model translatable.
- 4 different strategies for storing translations.

## 📺 Demo

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

- Laravel `7.0` or newer  
- PHP `7.2` or newer
- [Octane](https://github.com/laravel/octane) friendly

## 🔌 Installation

Install the package via composer.

```bash
composer require nevadskiy/laravel-translatable
```

## 📄 Documentation

### Making models translatable

Add the `HasTranslations` trait of the strategy you want to use to your model that you want to make translatable.

For example, let's use the **Additional Table Extended** strategy.

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Strategies\AdditionalTableExtended\HasTranslations;

class Book extends Model
{
    use HasTranslations;
}
```

And you also need to specify which attributes should be translatable using the `$translatable` array.

```php
/**
 * The attributes that are translatable.
 *
 * @var array
 */
protected $translatable = [
    'title',
    'description',
];
```

Final model may look like this.

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\Strategies\AdditionalTableExtended\HasTranslations;

class Book extends Model
{
    use HasTranslations; 

    protected $translatable = [
        'title', 
        'description',
    ];
}
```

### Strategies

The package provides 4 different strategies that determine how translations will be stored in the database.

- Single table strategy
- Single table extended strategy
- Additional table strategy
- Additional table extended strategy

The word "extended" in the strategy name indicates that this strategy can be added to existing models without having to change the structure of the table in the database, because the translations for the fallback locale are still stored in the original table, and only translations to custom locales are stored separately.

---image compare table structure for different strategy---

#### Single table strategy

With this strategy, translations for every model will be stored in the single global `translations` table.

The table structure:

| Column                 | Description                                    |
|------------------------|------------------------------------------------|
| id                     | ID of the translation                          |
| translatable_id        | Morph ID of the model of the translation       |
| translatable_type      | Morph type of the translatable model           |
| translatable_attribute | Attribute of the translatable model            |
| locale                 | Locale of the translation value                |
| value                  | The translation value                          |
| created_at             | The timestamp when the translation was created |
| updated_at             | The timestamp when the translation was updated |

##### Usage

Publish the strategy migration using the command:

```bash
php artisan vendor:publish --tag=translations-migration
```

Execute the `migrate` command:

```bash
php artisan migrate
```

## 📄 Documentation

Default locale values are stored in the original table as usual.

Values in non-default locales of each translatable model are stored in the single `translations` table.

The package takes the default locale from the `config('app.fallback_locale')` value.

### Strategies

#### Single Table

- all translations are in the single table
- consistent simple queries
- model can be created in any locale
- non-optimal indexes

#### Single Table Extended

- translations for default locale are in original table, for custom locale - in the global table
- complex queries
- model can be created only in fallback locale
- non-optimal indexes
- can use fallback translation when current is missing
- can be added to an existing model without restructuring the original table

##### Automatically store and retrieve translations of the model using translatable attributes

```php
$book = Book::where('title', 'Book about birds')->first();

app()->setLocale('uk');

$book->update(['title' => 'Книга о птицах']);

echo $book->title; // 'Книга о птицах'

app()->setLocale('en');

echo $book->title; // 'Book about birds'
```

##### Manually store and retrieve translations of the model

```php
$book = Book::where('title', 'Book about dolphins')->first();

$book->translate('title', 'Книга о дельфинах', 'uk');

echo $book->getTranslation('title', 'uk'); // 'Книга о дельфинах'
```

##### Methods for reading translation

| Method                    | Description                                                                                                                   |
|---------------------------|-------------------------------------------------------------------------------------------------------------------------------|
| `getTranslationOrDefault` | Retrieves a translation for the given attribute or a default value if a translation is missing.                               |
| `getTranslation`          | Retrieves a translation for the given attribute or `null` if a translation is missing.                                        |
| `getRawTranslation`       | Retrieves a translation without any Eloquent accessors applied for the given attribute or `null` if a translation is missing. |
| `getDefaultTranslation`   | Retrieves the value in a default locale.                                                                                      |

##### Translatable models creation

Note that translatable models will always be created in **default** locale even when the current locale is different.
Any translations can be attached only to **existing** models.  

```php
app()->setLocale('de');
Book::create(...); // This will persist model as usual with the default application locale.
```

##### Displaying collection of models

The package automatically eager loads translations of the current locale for you, so you can easily retrieve collection of models as usual.

```php
// In a controller
app()->setLocale('uk');
$books = Book::paginate(20);

// In a view
@foreach ($books as $book)
    {{ $book->title }} // Shows a title in the current locale OR in the default locale if a translation is missing.
@endforeach
```  

##### Translations work with model accessors

```php
class Book extends Model
{
    // ...

    public function getTitleAttribute()
    {
        return Str::ucfirst($this->attributes['title']);
    }
}

$book = Book::create(['title' => 'book about birds']);
$book->translate('title', 'книга о птицах', 'uk');

// Using attribute with the current locale
app()->setLocale('uk');
echo $book->title; // 'Книга о птицах'

// Using getTranslate method
echo $book->getTranslation('title', 'uk'); // 'Книга о птицах'
```

##### Translations work with model mutators as well

```php
class Book extends Model
{
    public function setDescriptionAttribute($description)
    {
        $this->attributes['description'] = Str::substr($description, 0, 10);
    }
}

$book = Book::create(['description' => 'Very long description']);
$book->translate('description', 'Очень длинное описание', 'uk');

// Using attribute with the current locale
app()->setLocale('uk');
echo $book->description; // 'Очень длин'

// Using getTranslation method
echo $book->getTranslation('description', 'uk'); // 'Очень длин'
```

##### Querying models without translations

Sometimes you may need to query translatable model without the `translations` relation. You can do this using `withoutTranslationsScope` scope.

```php
$books = Book::withoutTranslationsScope()->get();
```

##### Querying translations 

You can execute queries on translatable models by translatable attributes.  

```php
$books = Book::whereTranslatable('title', 'Книга о жирафах')->get();
```

> It will also work with values in default locale.

If you want to query rows only by a specific locale, you should pass it by yourself. 

$books = Book::whereTranslatable('title', 'Книга о жирафах', 'uk')->get();

Otherwise, the query builder will return matched rows within all available locales.

Also, you can use different operators for querying translations.

```php
$books = Book::whereTranslatable('title', 'Book about%', null, 'LIKE')->get();
// or
$books = Book::whereTranslatable('title', 'Книги о%', 'uk', 'LIKE')->get();
```

##### Ordering translations

Ordering models by a translatable attribute in the current locale.

```php
$books = Book::orderByTranslatable('title')->get();
```

Ordering models by a translatable attribute in the specific locale.

```php
$books = Book::orderByTranslatable('title', 'desc', 'de')->get();
```

For more complex queries - feel free to use [Laravel relation queries](https://laravel.com/docs/7.x/eloquent-relationships#querying-relationship-existence).

##### Disable auto loading

If you do not want to automatically load or save translations when you interact with a translatable property, you can disable the feature.

To disable it for a specific model, override the `getterAsTranslation` or `autoSaveTranslations` methods in your model like so.

```php
class Book extends Model
{
    use HasTranslations;

    protected $translatable = ['title'];

    public function getterAsTranslation()
    {
        return false;
    }

    public function autoSaveTranslations()
    {
        return false;
    }
}
```

Or globally for every model.

```php
use Nevadskiy\Translatable\Translatable;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app[Translatable::class]->disableAutoLoading();
        $this->app[Translatable::class]->disableAutoSaving();
    }
}
```

##### Route model binding

Translatable model can be easily resolved using **Route Model Binding** feature.

All you need to do to let Laravel resolve models by a translatable attribute is to set the needed locale that you want to be used for querying models **before** a request will reach `Illuminate\Routing\Middleware\SubstituteBindings::class` middleware.

The simplest solution is to create a new middleware, for example `SetLocaleMiddleware`, attach it to the route where you want to resolve translatable models, and register the middleware in the `$middlewarePriority` array of the `app/Http/Kernel.php` file above the `\Illuminate\Routing\Middleware\SubstituteBindings::class` class.


It may look like this:

```php
// app/Http/Middleware/SetLocaleMiddleware.php
public function handle($request, Closure $next)
{
    // Setting the current locale from cookie
    app()->setLocale($request->cookie('locale'));
}
```

```php
// app/Http/Kernel.php
protected $middlewareGroups = [
    'web' => [
        // ... default middleware stack
        \App\Http\Middleware\SetLocaleMiddleware::class, // <--- your middleware
        \Illuminate\Routing\Middleware\SubstituteBindings::class, // <--- bindings middleware
    ],
];

protected $middlewarePriority = [
    // ... default middleware stack
    \App\Http\Middleware\SetLocaleMiddleware::class, // <--- your middleware above
    \Illuminate\Routing\Middleware\SubstituteBindings::class, // <--- bindings middleware below
];
```

More about sorting middleware [here](https://laravel.com/docs/8.x/middleware#sorting-middleware).

```php
// routes/web.php
Route::get('posts/{post:slug}', 'BooksController@show');
```

```php
// app/Http/Controllers/BookController.php
public function show(Book $post)
{
    // Book model is resolved by translated slug using the current locale.
}
```

##### Using morph map

It is recommended to use `morph map` for all translatable models to minimize coupling between a database and application structure.

```php
use Illuminate\Database\Eloquent\Relations\Relation;

Relation::morphMap([
    'books' => Book::class,
    'categories' => Category::class,
]);
```
More about morph maps [here](https://laravel.com/docs/7.x/eloquent-relationships#custom-polymorphic-types).

## Links

- https://www.soluling.com/Help/Database/Index.htm
- https://medium.com/@cemalcanakgul/what-is-the-best-database-design-for-multi-language-data-b21982dd7265
- https://treewebsolutions.com/articles/multilanguage-database-design-in-mysql-6

## 📑 Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## ☕ Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for more information.

## 🔓 Security

If you discover any security related issues, please [e-mail me](mailto:nevadskiy@gmail.com) instead of using the issue tracker.

## 📜 License

The MIT License (MIT). Please see [LICENSE](LICENSE.md) for more information.

## 🔨 To Do

- [ ] add possibility to specify `boolean` argument in `whereTranslatable` scope (cover with test)
- [ ] add reverse `translatable` relation to the AdditionalTable strategy model
- [ ] cover `Translations` class with tests 
- [ ] add possibility to use translations in database model factories (consider adding Translations collection that handles by seeder `'name' => new Translations(['en' => '...', 'uk' => '...'])`) or add it directly to translator
- [ ] add possibility to eager load model with translations for all locales (useful for alternate route generation)
- [ ] add plugin for auto-translation (for example using google translator API).
- [ ] add link to nova package
- [ ] add possibility to check missing translations.
- [ ] add possibility to delete translations instead of setting `null` or empty string.
- [ ] add possibility to specify custom fallback locale per model in extended strategy.
- [ ] `replicate` method should replicate model with all translations.
- [ ] command to show missing translations
- [ ] add doc to show how to add custom strategy or extend existing
- [ ] add json translations strategy (similar to spatie-translations) 

- SingleTableStrategy
  - [ ] add possibility to specify fallback locale per model 
  - [ ] add possibility to disable fallback translations (return nulls if missing)
  - [ ] specify custom `Translation` model (for example to use UUID)
