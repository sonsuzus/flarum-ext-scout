<?php

namespace ClarkWinkelmann\Scout\Extend;

use ClarkWinkelmann\Scout\FlarumSearchableScope;
use ClarkWinkelmann\Scout\ScoutModelWrapper;
use Flarum\Extend\ExtenderInterface;
use Flarum\Extension\Extension;
use Flarum\Extension\ExtensionManager;
use Flarum\Foundation\ContainerUtil;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;

class Scout implements ExtenderInterface
{
    protected string $modelClass;
    protected array $attributes = [];
    protected array $searchable = [];
    protected array $listenSaved = [];
    protected array $listenDeleted = [];

    /**
     * @param string $modelClass The ::class attribute of the Eloquent model you are modifying.
     *                           This model should extend from \Flarum\Database\AbstractModel.
     */
    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
    }

    /**
     * Add to or modify the searchable attributes array of this model.
     *
     * @param callable|string $callback
     * @return self
     */
    public function attributes($callback): self
    {
        $this->attributes[] = $callback;

        return $this;
    }

    /**
     * Modify whether a given model should be searchable.
     *
     * @param callable|string $callback
     * @return self
     */
    public function searchable($callback): self
    {
        $this->searchable[] = $callback;

        return $this;
    }

    /**
     * Register an event listener that should cause Scout to update the indexed data.
     *
     * @param string $eventClass The class to listen for on the event dispatcher
     * @param callable|string $callback
     * @return self
     */
    public function listenSaved(string $eventClass, $callback): self
    {
        $this->listenSaved[] = [
            $eventClass,
            $callback,
        ];

        return $this;
    }

    /**
     * Register an event listener that should cause Scout to remove the indexed data.
     *
     * @param string $eventClass The class to listen for on the event dispatcher
     * @param callable|string $callback
     * @return self
     */
    public function listenDeleted(string $eventClass, $callback): self
    {
        $this->listenDeleted[] = [
            $eventClass,
            $callback,
        ];

        return $this;
    }

    public function extend(Container $container, ?Extension $extension = null): void
    {
        if (!class_exists($this->modelClass)) {
            return;
        }

        /**
         * @var ExtensionManager $manager
         */
        $manager = $container->make(ExtensionManager::class);

        // By design extensions will always call the extender if Scout is installed without checking if it's enabled
        // We could continue setting the container bindings which would just never be used
        // But the model scope and event listeners must be skipped if the extension is disabled
        if (!$manager->isEnabled('dshovchko-scout')) {
            return;
        }

        // It looks like we can safely repeat this call, a new scope instance of the same scope class will just
        // override the existing scope of the same class
        $this->modelClass::addGlobalScope(new FlarumSearchableScope());

        if (count($this->attributes)) {
            $container->extend('scout.attributes', function (array $attributes) use ($container) {
                foreach ($this->attributes as $callback) {
                    $attributes[$this->modelClass][] = ContainerUtil::wrapCallback($callback, $container);
                }

                return $attributes;
            });
        }

        if (count($this->searchable)) {
            $container->extend('scout.searchable', function (array $searchable) use ($container) {
                foreach ($this->searchable as $callback) {
                    $searchable[$this->modelClass][] = ContainerUtil::wrapCallback($callback, $container);
                }

                return $searchable;
            });
        }

        if (count($this->listenSaved) || count($this->listenDeleted)) {
            // Same event registration logic as in Flarum's Extend\Event extender
            $events = $container->make(Dispatcher::class);
            $app = $container->make('flarum');

            $app->booted(function () use ($events, $container) {
                foreach ($this->listenSaved as $listener) {
                    $events->listen($listener[0], function ($event) use ($listener, $container) {
                        $model = ContainerUtil::wrapCallback($listener[1], $container)($event);

                        if ($model) {
                            (new ScoutModelWrapper($model))->scoutObserverSaved();
                        }
                    });
                }

                foreach ($this->listenDeleted as $listener) {
                    $events->listen($listener[0], function ($event) use ($listener, $container) {
                        $model = ContainerUtil::wrapCallback($listener[1], $container)($event);

                        if ($model) {
                            (new ScoutModelWrapper($model))->scoutObserverDeleted();
                        }
                    });
                }
            });
        }
    }
}
