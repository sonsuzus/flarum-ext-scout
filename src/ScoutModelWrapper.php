<?php

namespace ClarkWinkelmann\Scout;

use Flarum\Database\AbstractModel;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as BaseCollection;
use Laravel\Scout\EngineManager;
use Laravel\Scout\SearchableScope;

#[\AllowDynamicProperties]
class ScoutModelWrapper
{
    public AbstractModel $model;

    public function __construct(AbstractModel $model)
    {
        $this->model = $model;
    }

    public function __get(string $name): mixed
    {
        return $this->model->$name;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->model->$name = $value;
    }

    public function __isset(string $name): bool
    {
        return isset($this->model->$name);
    }

    public function __call(string $name, array $arguments): mixed
    {
        return $this->model->$name(...$arguments);
    }

    public static function registerSearchableMacros(): void
    {
        BaseCollection::macro('searchable', function () {
            /** @var BaseCollection $this */
            $this->pipe(function (BaseCollection $models) {
                return $models->map(function ($model) {
                    if ($model instanceof ScoutModelWrapper) {
                        return $model;
                    }

                    return new ScoutModelWrapper($model);
                });
            })->first()?->queueMakeSearchable($this);
        });

        BaseCollection::macro('unsearchable', function () {
            /** @var BaseCollection $this */
            $this->pipe(function (BaseCollection $models) {
                return $models->map(function ($model) {
                    if ($model instanceof ScoutModelWrapper) {
                        return $model;
                    }

                    return new ScoutModelWrapper($model);
                });
            })->first()?->queueRemoveFromSearch($this);
        });
    }

    public function queueMakeSearchable(BaseCollection $models): void
    {
        if ($models->isEmpty()) {
            return;
        }

        $wrapped = $models->map(function ($model) {
            return $model instanceof self ? $model : new self($model);
        });

        if (self::usesQueue()) {
            dispatch(new Job\MakeSearchable($wrapped));
        } else {
            (new Job\MakeSearchable($wrapped))->handle();
        }
    }

    public function queueRemoveFromSearch(BaseCollection $models): void
    {
        if ($models->isEmpty()) {
            return;
        }

        $wrapped = $models->map(function ($model) {
            return $model instanceof self ? $model : new self($model);
        });

        if (self::usesQueue()) {
            dispatch(new Job\RemoveFromSearch($wrapped));
        } else {
            (new Job\RemoveFromSearch($wrapped))->handle();
        }
    }

    protected static function usesQueue(): bool
    {
        /** @var SettingsRepositoryInterface $settings */
        $settings = resolve(SettingsRepositoryInterface::class);

        return (bool)$settings->get('clarkwinkelmann-scout.queue');
    }

    public function shouldBeSearchable(): bool
    {
        $callbacks = Arr::get(resolve('scout.ShouldBeSearchable'), get_class($this->model), []);

        foreach ($callbacks as $callback) {
            if ($callback($this->model) === false) {
                return false;
            }
        }

        return true;
    }

    public function searchIndexShouldBeUpdated(): bool
    {
        return true;
    }

    public function searchable(): void
    {
        $this->queueMakeSearchable(BaseCollection::make([$this]));
    }

    public function unsearchable(): void
    {
        $this->queueRemoveFromSearch(BaseCollection::make([$this]));
    }

    public function wasSearchableBeforeUpdate(): bool
    {
        return true;
    }

    public function wasSearchableBeforeDelete(): bool
    {
        return true;
    }

    public function getScoutModelsByIds(\Laravel\Scout\Builder $builder, array $ids): BaseCollection
    {
        return $this->queryScoutModelsByIds($builder, $ids)->get();
    }

    public function queryScoutModelsByIds(\Laravel\Scout\Builder $builder, array $ids): \Illuminate\Database\Eloquent\Builder
    {
        $query = $this->model->newQuery();

        if ($builder->queryCallback) {
            call_user_func($builder->queryCallback, $query);
        }

        $whereIn = in_array($this->model->getKeyType(), ['int', 'integer'])
            ? 'whereIntegerInRaw'
            : 'whereIn';

        return $query->{$whereIn}($this->getScoutKeyName(), $ids);
    }

    public function searchableAs(): string
    {
        /** @var SettingsRepositoryInterface $settings */
        $settings = resolve(SettingsRepositoryInterface::class);

        return $settings->get('clarkwinkelmann-scout.prefix', '') . $this->model->getTable();
    }

    public function toSearchableArray(): array
    {
        $attributes = [];

        $callbacks = Arr::get(resolve('scout.attributes'), get_class($this->model), []);

        foreach ($callbacks as $callback) {
            $attributes = array_merge($attributes, $callback($this->model));
        }

        return $attributes;
    }

    public function syncWithSearchUsing(): string
    {
        return config('queue.default');
    }

    public function syncWithSearchUsingQueue(): ?string
    {
        return null;
    }

    public function pushSoftDeleteMetadata(): static
    {
        return $this;
    }

    public function scoutMetadata(): array
    {
        return [];
    }

    public function getScoutKey(): mixed
    {
        return $this->model->getKey();
    }

    public function getScoutKeyName(): string
    {
        return $this->model->getQualifiedKeyName();
    }

    public function searchableUsing(): \Laravel\Scout\Engines\Engine
    {
        return resolve(EngineManager::class)->engine();
    }
}