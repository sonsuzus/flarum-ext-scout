<?php

namespace ClarkWinkelmann\Scout;

use Flarum\Discussion\Discussion;
use Flarum\Discussion\Event as DiscussionEvent;
use Flarum\Discussion\Search\DiscussionSearcher;
use Flarum\Extend;
use Flarum\Post\CommentPost;
use Flarum\Post\Event as PostEvent;
use Flarum\Post\Post;
use Flarum\User\Event as UserEvent;
use Flarum\User\Search\UserSearcher;
use Flarum\User\User;

$searchExtenders = [];

if (class_exists(\Flarum\Extend\SearchDriver::class) && class_exists(\Flarum\Search\Database\DatabaseSearchDriver::class)) {
    $searchExtenders[] = (new Extend\SearchDriver(\Flarum\Search\Database\DatabaseSearchDriver::class))
        ->setFulltext(DiscussionSearcher::class, Search\DiscussionGambit::class)
        ->setFulltext(UserSearcher::class, Search\UserGambit::class);
} elseif (class_exists(\Flarum\Extend\SimpleFlarumSearch::class)) {
    $searchExtenders[] = (new Extend\SimpleFlarumSearch(DiscussionSearcher::class))
        ->setFullTextGambit(Search\DiscussionGambit::class);
    $searchExtenders[] = (new Extend\SimpleFlarumSearch(UserSearcher::class))
        ->setFullTextGambit(Search\UserGambit::class);
}

return array_merge([
    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js'),

    new Extend\Locales(__DIR__ . '/resources/locale'),

    (new Extend\ServiceProvider())
        ->register(ScoutServiceProvider::class),

    (new Extend\Console())
        ->command(Console\FlushCommand::class)
        ->command(Console\ImportAllCommand::class)
        ->command(Console\ImportCommand::class),

    (new Extend\Event())
        ->listen(DiscussionEvent\Deleting::class, Listener\DeletingDiscussion::class),

    (new Extend\Settings())
        ->serializeToForum('scoutQueryMinLength', 'clarkwinkelmann-scout.queryMinLength', function ($value): ?int {
            return $value ? (int)$value : null;
        }),

    (new Extend\Scout(Discussion::class))
        ->listenSaved(DiscussionEvent\Started::class, function (DiscussionEvent\Started $event): Discussion {
            return $event->discussion;
        })
        ->listenSaved(DiscussionEvent\Renamed::class, function (DiscussionEvent\Renamed $event): Discussion {
            return $event->discussion;
        })
        ->listenSaved(DiscussionEvent\Hidden::class, function (DiscussionEvent\Hidden $event): Discussion {
            return $event->discussion;
        })
        ->listenSaved(DiscussionEvent\Restored::class, function (DiscussionEvent\Restored $event): Discussion {
            return $event->discussion;
        })
        ->listenDeleted(DiscussionEvent\Deleted::class, function (DiscussionEvent\Deleted $event): Discussion {
            return $event->discussion;
        })
        ->attributes(function (Discussion $discussion): array {
            return [
                'id' => $discussion->id,
                'title' => $discussion->title,
            ];
        }),

    (new Extend\Scout(Post::class))
        ->listenSaved(DiscussionEvent\Started::class, function (DiscussionEvent\Started $event): ?Post {
            return $event->discussion->firstPost;
        })
        ->listenSaved(PostEvent\Posted::class, function (PostEvent\Posted $event): Post {
            return $event->post;
        })
        ->listenSaved(PostEvent\Revised::class, function (PostEvent\Revised $event): Post {
            return $event->post;
        })
        ->listenSaved(PostEvent\Hidden::class, function (PostEvent\Hidden $event): Post {
            return $event->post;
        })
        ->listenSaved(PostEvent\Restored::class, function (PostEvent\Restored $event): Post {
            return $event->post;
        })
        ->listenDeleted(PostEvent\Deleted::class, function (PostEvent\Deleted $event): Post {
            return $event->post;
        })
        ->shouldBeSearchable(function (Post $post): bool {
            return $post->type === 'comment' && $post->is_private === false && $post->hidden_at === null;
        })
        ->attributes(function (Post $post): array {
            return [
                'id' => $post->id,
                'discussion_id' => $post->discussion_id,
                'content' => $post instanceof CommentPost ? strip_tags($post->formatContent()) : $post->content,
            ];
        }),

    (new Extend\Scout(User::class))
        ->listenSaved(UserEvent\Registered::class, function (UserEvent\Registered $event): User {
            return $event->user;
        })
        ->listenSaved(UserEvent\Saving::class, function (UserEvent\Saving $event): ?User {
            if ($event->user->isDirty(['username', 'display_name', 'bio'])) {
                $event->user->afterSave(function (User $user) {
                    $wrapper = new ScoutModelWrapper($user);
                    $wrapper->searchable();
                });
            }

            return null;
        })
        ->listenDeleted(UserEvent\Deleted::class, function (UserEvent\Deleted $event): User {
            return $event->user;
        })
        ->attributes(function (User $user): array {
            $attributes = [
                'id' => $user->id,
                'displayName' => $user->display_name,
                'username' => $user->username,
            ];

            if ($user->bio) {
                $attributes['bio'] = $user->bio;
            }

            return $attributes;
        }),
], $searchExtenders);