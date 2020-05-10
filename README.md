# Laravel Translatable  
The package add provides possibility to translate your Eloquent models into different languages.

## Features 
- Simple and intuitive API
- No need to rewrite existing migrations, models or views
- Storing all translations in the single 'translations' table
- Works with model accessors & mutators
- Works with model casts (even with JSON structures)
- Eager loads only needed translations
- Well suitable for already existing projects
- Provides useful events


## Demo
```
$post = Book::create(['title' => 'Book about giraffes']);

// Storing translations
app()->setLocale('es')
$book->title = 'Libro sobre jirafas';
$book->save();

// Accessing translations
echo $book->title; // 'Libro sobre jirafas'
app()->setLocale('en');
echo $book->title; // 'Book about giraffes'
```


## Installation
1. Install a package via composer
```
composer require nevadskiy/laravel-translations
```

2. Add a `HasTranslations` trait to your Models
```
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\HasTranslations;

class Post extends Model
{
    use HasTranslations;
}
```

3. Add a `$translatable` array to your models with attributes you want to be translatable.
```
use HasTranslations;

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

4. Also, make sure to have these attributes in the `$fillable` array
```
/**
 * The attributes that are mass assignable.
 *
 * @var array
 */
protected $fillable = [
    'title',
    'description',
];
```


## Documentation
Default language values are stored in the original table as usual.

Values in non default languages of every model are stored in the single `translations` table.

The package takes the default language from the `config('config.app.fallback_locale')` value.

##### Translatable model may look like
```
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Nevadskiy\Translatable\HasTranslations;

class Post extends Model
{
    use HasTranslations; 

    protected $guarded = [];

    protected $translatable = [
        'title', 
        'description',
    ];
}
```

##### Manually store and retrieve translations of the model
```
$post = Post::where('title', 'Post about dolphins')->first();

$post->translate('title', 'Пост о дельфинах', 'ru');

echo $post->getTranslation('title', 'ru'); // 'Пост о дельфинах'
```

##### Automatically store and retrieve translations of the model using translatable attributes
```
$post = Post::where('title', 'Post about birds')->first();

app()->setLocale('ru');
$post->title = 'Пост о птицах';
$post->save();

echo $post->title; // 'Пост о птицах'
app()->setLocale('en');
echo $post->title; // 'Post about birds'
```

##### Model creation
Note that translatable models will always be created in **default** locale even when current locale is different.
All translations can be attached only to **existing** models.  

##### Displaying collection of models
The package automatically eager loads translations of the current locale for you, so you can easily retrieve collection of models as usual
```
// In controller
app()->setLocale('ru')
$posts = Post::paginate(20);

// In your views
@foreach ($posts as $post)
    {{ $post->title }} // Shows title in the current locale OR in default locale if translation is missing.
@endforeach
```  

##### Translations work with model accessors
```
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

// Using attribute
app()->setLocale('ru');
echo $post->title; // 'Пост о птицах'

// Using getTranslate method
echo $post->getTranslation('title', 'ru'); // 'Пост о птицах'
```

##### Translations work with model mutators as well
```
class Post extends Model
{
    // ...

    public function setDesciptionAttribute($descrition)
    {
        $this->attributes['descrition'] = Str::substr($description, 0, 10);
    }
}

$post = Post::create(['description' => 'Very long description']);
$post->translate('description', 'Очень длинное описание', 'ru');

// Using attribute
app()->setLocale('ru');
echo $post->description; // 'Очень длин'

// Using getTranslation method
echo $post->getTranslation('description', 'ru'); // 'Очень длин'
```
