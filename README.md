# Translatable-models

Package installation does not affect original behaviour. You do not need to make any migrations for existing tables. 
All translated data are stored in the separate package's provided 'translations' table.

## Usage
Add HasTranslationsTrait

Add $translatable array
```
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

Make sure your translatable attributes are $fillable

#### Add docs for
mutations
accessors
array casts
eager loading
saving
reading
get original

##### TODO
- [ ] add possibility to remove translations eager loading...
- [ ] fix that translatable attributes is can be set through non $translatable fields
- [ ] refactor full translator class.
- [ ] add available locales array (probably dont)
- [ ] add remove unused translations console command (check if relation still exists and also add removal event listener for trait)
- [ ] check translation usage on ManyToMany (Pivot) models
- [ ] Add setTranslation() and getTranslation() methods
- [ ] FIX case when locale was changed multiple times (probably just clear translated[] array on translatable model)
- [ ] ADD guard when for model creating for non default locale...
- [ ] Feature command for pruning translations for undefined translatable model (removed, etc.)
- [ ] ADD SCOPE ONLY FOR CURRENT LOCALE EAGER LOADING. DONT LOAD ALL LOCALES 
- [ ] Add timestamp touching if translation was updated and throw updatedAt event when translation was added 
- [ ] Add translation events
- [ ] Feature remove listener for removing translations for removed item (resolve with softDelete)
- [ ] Fix nullable strings issues
- [ ] Add possibility to ignore mutators & accessors for translations
- [ ] Add possibility to simple API storing 
- [ ] Add more API engine drivers
- [ ] Add possibility to use auto-translation on updated\created events
