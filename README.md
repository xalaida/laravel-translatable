# Laravel Translatable

[![Tests](https://github.com/nevadskiy/laravel-translatable/workflows/Tests/badge.svg)](https://packagist.org/packages/nevadskiy/laravel-translatable)
[![Code Coverage](https://codecov.io/gh/nevadskiy/laravel-translatable/branch/master/graphs/badge.svg?branch=master)](https://packagist.org/packages/nevadskiy/laravel-translatable)
[![License](https://poser.pugx.org/nevadskiy/laravel-translatable/license)](https://packagist.org/packages/nevadskiy/laravel-translatable)
[![Latest Stable Version](https://poser.pugx.org/nevadskiy/laravel-translatable/v)](https://packagist.org/packages/nevadskiy/laravel-translatable)

The package provides possibility to translate your Eloquent models into different languages using a single database table.


## 🍬 Features 

- Auto-resolving model translations for the current locale.
- No need to rewrite existing migrations, models or views.
- Store all translations in the single 'translations' table.
- Works with model accessors & mutators & casts, even with JSON.
- Works with route model binding.
- Archive translations to improve searching experience.
- Provides useful events.


## ⚙️ Demo

```php
$book = Book::create(['title' => 'Book about giraffes']);

// Storing translations
app()->setLocale('es');
$book->title = 'Libro sobre jirafas';
$book->save();

// Reading translations
app()->setLocale('es');
echo $book->title; // 'Libro sobre jirafas'

app()->setLocale('en');
echo $book->title; // 'Book about giraffes'
```


## ✅ Requirements

- Laravel `7.0` or newer  
- PHP `7.2` or newer


## 🔌 Installation

1. Install the package via composer.

```bash
composer require nevadskiy/laravel-translatable
```

2. Optional. If you are not going to use translations for models with UUID primary keys, make the following:

- Publish package migration

```bash
php artisan vendor:publish --tag=translatable
```

- Replace the line `$table->uuidMorphs('translatable');` with `$table->morphs('translatable');` in the published migration.

3. Run the migration command.

```bash
php artisan migrate
```


## 🔨 Making models translatable 

1. Add the `HasTranslations` trait to your models which you want to make translatable.

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\HasTranslations;

class Post extends Model
{
    use HasTranslations;
}
```

2. Add the `$translatable` array to your models with attributes you want to be translatable.

```php
/**
 * The attributes that can be translatable.
 *
 * @var array
 */
protected $translatable = [
    'title',
    'description',
];
```

#### Final model may look like this

```php
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\HasTranslations;

class Post extends Model
{
    use HasTranslations; 

    protected $translatable = [
        'title', 
        'description',
    ];
}
```


## 📄 Documentation

Default locale values are stored in the original table as usual.

Values in non-default locales of each translatable model are stored in the single `translations` table.

The package takes the default locale from the `config('app.fallback_locale')` value.

##### Automatically store and retrieve translations of the model using translatable attributes

```php
$book = Book::where('title', 'Book about birds')->first();

app()->setLocale('ru');

$book->update(['title' => 'Книга о птицах']);

echo $book->title; // 'Книга о птицах'

app()->setLocale('en');

echo $book->title; // 'Book about birds'
```

##### Manually store and retrieve translations of the model

```php
$book = Book::where('title', 'Book about dolphins')->first();

$book->translate('title', 'Книга о дельфинах', 'ru');

echo $book->getTranslation('title', 'ru'); // 'Книга о дельфинах'
```

##### Methods for reading translation

Method | Description
--- | ---
`getTranslationOrDefault` | Retrieves a translation for the given attribute or a default value if a translation is missing.
`getTranslation` | Retrieves a translation for the given attribute or `null` if a translation is missing.
`getRawTranslation` | Retrieves a translation without any Eloquent accessors applied for the given attribute or `null` if a translation is missing.
`getDefaultTranslation` | Retrieves the value in a default locale.


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
app()->setLocale('ru');
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
$book->translate('title', 'книга о птицах', 'ru');

// Using attribute with the current locale
app()->setLocale('ru');
echo $book->title; // 'Книга о птицах'

// Using getTranslate method
echo $book->getTranslation('title', 'ru'); // 'Книга о птицах'
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
$book->translate('description', 'Очень длинное описание', 'ru');

// Using attribute with the current locale
app()->setLocale('ru');
echo $book->description; // 'Очень длин'

// Using getTranslation method
echo $book->getTranslation('description', 'ru'); // 'Очень длин'
```

##### Removing unused translations

The package automatically remove translations of deleted models respecting softDeletes, but if translatable models have been removed using query builder, their translations will exist in the database.
To manually remove all unused translations, run the `php artisan translatable:remove-unused` command.

##### Querying models without translations

Sometimes you may need to query translatable model without the `translations` relation. You can do this using `withoutTranslations` scope.

```php
$books = Book::withoutTranslations()->get();
```

##### Querying translations 

You can execute queries on translatable models by translatable attributes.  

```php
$books = Book::whereTranslatable('title', 'Книга о жирафах')->get();
```

> It will also work with values in default locale.

If you want to query rows only by a specific locale, you should pass it by yourself. 

$books = Book::whereTranslatable('title', 'Книга о жирафах', 'ru')->get();

Otherwise, the query builder will return matched rows within all available locales.

Also, you can use different operators for querying translations.

```php
$books = Book::whereTranslatable('title', 'Book about%', null, 'LIKE')->get();
// or
$books = Book::whereTranslatable('title', 'Книги о%', 'ru', 'LIKE')->get();
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

To disable it for a specific model, override the `autoLoadTranslations` or `autoSaveTranslations` methods in your model like so.

```php
class Post extends Model
{
    use HasTranslations;

    protected $translatable = ['title'];

    public function autoLoadTranslations()
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


##### Archiving translations

Sometimes it can be useful to archive some translations that will not be resolved automatically, but can be used for searching functionality.
For example, you may store archived translation manually using the following code:

```php
$post = Post::first();
$post->archiveTranslation('title', 'Old title', 'en');
```

Now `Old title` is associated with a post that allows to find the post using `whereTranslatable` scope:

```php
Post::whereTranslatable('title', 'Old title')->get();
```

You can also pass `null` as a third argument to the `archiveTranslation` method when a locale is unknown. 
If you do not pass `null`, the current locale will be used instead.

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
Route::get('posts/{post:slug}', 'PostsController@show');
```

```php
// app/Http/Controllers/PostController.php
public function show(Post $post)
{
    // Post model is resolved by translated slug using the current locale.
}
```

##### Using morph map

It is recommended to use `morph map` for all translatable models to minimize coupling between a database and application structure.

```php
use Illuminate\Database\Eloquent\Relations\Relation;

Relation::morphMap([
    'posts' => Post::class,
    'categories' => Category::class,
]);
```
More about morph maps [here](https://laravel.com/docs/7.x/eloquent-relationships#custom-polymorphic-types).


## Approaches

- https://www.soluling.com/Help/Database/Index.htm
- https://medium.com/@cemalcanakgul/what-is-the-best-database-design-for-multi-language-data-b21982dd7265
- https://treewebsolutions.com/articles/multilanguage-database-design-in-mysql-6

## 📑 Changelog

Please see [CHANGELOG](.github/CHANGELOG.md) for more information what has changed recently.


## ☕ Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for more information.


## 🔓 Security

If you discover any security related issues, please [e-mail me](mailto:nevadskiy@gmail.com) instead of using the issue tracker.


## 📜 License

The MIT License (MIT). Please see [LICENSE](LICENSE.md) for more information.
