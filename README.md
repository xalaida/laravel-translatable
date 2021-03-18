# Laravel Translatable

[![Tests](https://github.com/nevadskiy/laravel-translatable/workflows/Tests/badge.svg)](https://packagist.org/packages/nevadskiy/laravel-translatable)
[![Code Coverage](https://codecov.io/gh/nevadskiy/laravel-translatable/branch/master/graphs/badge.svg?branch=master)](https://packagist.org/packages/nevadskiy/laravel-translatable)
[![License](https://poser.pugx.org/nevadskiy/laravel-translatable/license)](https://packagist.org/packages/nevadskiy/laravel-translatable)
[![Latest Stable Version](https://poser.pugx.org/nevadskiy/laravel-uuid/v)](https://packagist.org/packages/nevadskiy/laravel-translatable)

The package provides possibility to translate your Eloquent models into different languages using a single database table.


## Features 

- Auto-resolving model translations for the current locale.
- No need to rewrite existing migrations, models or views.
- Store all translations in the single 'translations' table.
- Works with model accessors & mutators & casts, even with JSON.
- Works with route model binding.
- Archive translations to improve searching experience.
- Provides useful events.


## Demo

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


## Requirements

- Laravel `7.0` or newer  
- PHP `7.2` or newer


## Installation

1. Install a package via composer.
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


## Making models translatable 

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


## Documentation

Default locale values are stored in the original table as usual.

Values in non-default locales of each translatable model are stored in the single `translations` table.

The package takes the default locale from the `config('app.fallback_locale')` value.

##### Automatically store and retrieve translations of the model using translatable attributes

```php
$post = Post::where('title', 'Post about birds')->first();

app()->setLocale('ru');

$post->update(['title' => 'Пост о птицах']);

echo $post->title; // 'Пост о птицах'

app()->setLocale('en');

echo $post->title; // 'Post about birds'
```

##### Manually store and retrieve translations of the model

```php
$post = Post::where('title', 'Post about dolphins')->first();

$post->translate('title', 'Пост о дельфинах', 'ru');

echo $post->getTranslation('title', 'ru'); // 'Пост о дельфинах'
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
Book::create(...); // This will persist model as usual with the default locale.
```

##### Displaying collection of models

The package automatically eager loads translations of the current locale for you, so you can easily retrieve collection of models as usual.
```php
// In a controller
app()->setLocale('ru');
$posts = Post::paginate(20);

// In a view
@foreach ($posts as $post)
    {{ $post->title }} // Shows title in the current locale OR in default locale if translation is missing.
@endforeach
```  

##### Translations work with model accessors

```php
class Post extends Model
{
    // ...

    public function getTitleAttribute()
    {
        return Str::ucfirst($this->attributes['title']);
    }
}

$post = Post::create(['title' => 'post about birds']);
$post->translate('title', 'пост о птицах', 'ru');

// Using attribute with the current locale
app()->setLocale('ru');
echo $post->title; // 'Пост о птицах'

// Using getTranslate method
echo $post->getTranslation('title', 'ru'); // 'Пост о птицах'
```

##### Translations work with model mutators as well

Note that mutators should return the model instances.

```php
class Post extends Model
{
    public function setDescriptionAttribute($description)
    {
        $this->attributes['description'] = Str::substr($description, 0, 10);

        return $this;
    }
}

$post = Post::create(['description' => 'Very long description']);
$post->translate('description', 'Очень длинное описание', 'ru');

// Using attribute with the current locale
app()->setLocale('ru');
echo $post->description; // 'Очень длин'

// Using getTranslation method
echo $post->getTranslation('description', 'ru'); // 'Очень длин'
```

##### Removing unused translations

The package automatically remove translations of deleted models respecting softDeletes, but if translatable models have been removed using query builder, their translations would exist in the database.
To manually remove all unused translations, run the `php artisan translatable:remove-unused` command.

##### Querying models without translations

Sometimes you may need to query translatable model without the `translations` relation. You can do this using `withoutTranslations` scope.

```php
$books = Book::withoutTranslations()->get();
```

##### Querying translations 

Query models by translatable attributes. It also includes values in the default locale.  

```php
$books = Book::whereTranslatable('title', 'Книга о жирафах')->get();
```

If you want to query rows only by a specific locale, you should pass it by yourself. 
Otherwise, the scope will return matched rows within all locales.

```php
$books = Book::whereTranslatable('title', 'Книга о жирафах', 'ru')->get();
``` 

Also, you can use different operators for querying translations.
```php
$books = Book::whereTranslatable('title', 'Book about %', null, 'LIKE')->get();
```

Or using a specific locale.
```php
$books = Book::whereTranslatable('title', 'Книги о %', 'ru', 'LIKE')->get();
```

##### Ordering translations

Ordering models by a translatable attribute.
```php
$books = Book::orderByTranslatable('title')->get();
```

Ordering models by a translatable attribute in the specific locale.

```php
$books = Book::orderByTranslatable('title', 'desc', 'de')->get();
```

For more complex queries - feel free to use [Laravel relation queries](https://laravel.com/docs/7.x/eloquent-relationships#querying-relationship-existence).

##### Archiving translations

Sometimes it can be useful to archive some translations that will not be resolved automatically in the views, but can be used for searching functionality.
For example, you may store archived translation manually using the following code:
```php
$post = Post::first();
$post->archiveTranslation('title', 'Old title', 'en');
```

Now `Old title` is associated with a post that allows to find the post using `whereTranslatable` scope:
```php
Post::whereTranslatable('title', 'Old title')->get();
```

You can also pass `null` as third argument to `archiveTranslation` method when a locale is unknown.

##### Route model binding

Translatable model can be easily resolved using **Route Model Binding** feature.

All you need to do to let Laravel resolve models by a translatable attribute is to set the needed locale which you want to be used for querying models **before** a request will reach `Illuminate\Routing\Middleware\SubstituteBindings::class` middleware.

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

```php
// routes/web.php
Route::get('posts/{post:slug}', 'PostsController@show')
```

```php
// app/Http/Controllers/PostController.php
public function show(Post $post)
{
    // Post model is resolved by translated slug using the current locale   
}
```

##### Using morph map

It is recommended to use `morph map` for all translatable models to minimize coupling between database and application structure.

```php
use Illuminate\Database\Eloquent\Relations\Relation;

Relation::morphMap([
    'posts' => Post::class,
    'categories' => Category::class,
]);
```
[Learn more](https://laravel.com/docs/7.x/eloquent-relationships#custom-polymorphic-types)


## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.


## Contributing

Any contribution is **Welcome**.

Please see [CONTRIBUTING](CONTRIBUTING.md) for more information.


## Security

If you discover any security related issues, please [e-mail me](mailto:nevadskiy@gmail.com) instead of using the issue tracker.


## License

The MIT License (MIT). Please see [LICENSE](LICENSE.md) for more information.
