<?php

namespace Nevadskiy\Translatable\Strategies;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;

/**
 * TODO: make it configurable to use timestamps or no.
 */
class AdditionalTableStrategy implements TranslatorStrategy
{
    /**
     * The translatable model instance.
     *
     * @var Model
     */
    private $model;

    /**
     * The database connection instance.
     *
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * Make a new strategy instance.
     *
     * TODO: probably swap Model with 'Translatable' interface to decouple dependencies.
     */
    public function __construct(Model $model, ConnectionInterface $connection)
    {
        $this->model = $model;
        $this->connection = $connection;
    }

    public function get(string $attribute, string $locale)
    {
        // $this->connection->table();

        return 'Свитер с оленями';
    }

    // TODO: possible 'nullable' insert error case here for multiple fields.
    public function set(string $attribute, $value, string $locale): bool
    {
        return $this->table()->updateOrInsert([
            // TODO: make the foreign key configurable.
            $this->model->getForeignKey() => $this->model->getKey(),
            'locale' => $locale
        ], [
            // TODO: add timestamps.
            // TODO: extract into payload method.
            'id' => Str::uuid()->toString(),
            $attribute => $value,
        ]);
    }

    /**
     * Begin a new database query against the table.
     */
    protected function table(): Builder
    {
        return $this->connection->table($this->getTranslationsTable());
    }

    /**
     * Get the database table of translations of the model.
     *
     * TODO: add possibility to override the table name (maybe introduce configureStrategy hook)
     */
    protected function getTranslationsTable(): string
    {
        return $this->model->joiningTableSegment() . '_translations';
    }
}
