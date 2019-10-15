# Translatable-models

Package installation does not affect original behaviour. You do not need to make any migrations for existing tables. 
All translated data are stored in the separate package's provided 'translations' table.

## Usage
Add HasTranslationsTrait

Set $translatable array

Set $fillable array contains translatable attributes

#### Add docs for
mutations
accessors
array casts
eager loading
saving
reading
get original

##### TODO
- Add timestamp touching if translation was updated and throw updatedAt event when translation was added 
- Add translation events
- Feature remove listener for removing translations for removed item (resolve with softDelete)
- Fix nullable strings issues
- Add possibility to ignore mutators & accessors for translations
- Add possibility to simple API storing 
- Add more API engine drivers
- Add possibility to use auto-translation on updated\created events
