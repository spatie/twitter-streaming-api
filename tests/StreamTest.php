<?php

namespace Spatie\TwitterStreamingApi\Test;

use Felix\TwitterStream\Exceptions\TwitterException;
use Felix\TwitterStream\Streams\FilteredStream;
use Felix\TwitterStream\TwitterConnection;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\FnStream;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Spatie\TwitterStreamingApi\PublicStream;
use Spatie\TwitterStreamingApi\UserStream;

class StreamTest extends TestCase
{
    protected array $requests = [];

    #[DataProvider('filters')]
    public function test_it_replaces_rules_and_parses_post_envelopes(string $method, string|array $filter, string $expectedRule): void
    {
        $connection = $this->connection([
            new Response(200, [], '{"data":[{"id":"41","value":"old"},{"id":"73","value":"other"}]}'),
            new Response(200, [], '{}'),
            new Response(201, [], '{"data":[{"id":"88","value":"new"}]}'),
            $this->posts(),
        ]);
        $received = [];

        PublicStream::create($connection)
            ->{$method}($filter, function (object $post) use (&$received) {
                $received[] = $post;
            })
            ->setLocale('nl')
            ->startListening((new FilteredStream())->withTweetLimit(2)->fields(['tweet' => ['author_id']])->expansions('author_id'));

        $this->assertSame(['234', '567'], array_map(fn (object $post) => $post->data->id, $received));
        $this->assertSame('Hello 🐘', $received[0]->data->text);
        $this->assertSame('writer', $received[0]->includes->users[0]->username);
        $this->assertSame('88', $received[0]->matching_rules[0]->id);
        $this->assertCount(4, $this->requests);
        $this->assertSame('GET', $this->requests[0]['request']->getMethod());
        $this->assertSame('https://api.twitter.com/2/tweets/search/stream/rules', (string) $this->requests[0]['request']->getUri());
        $this->assertSame(['delete' => ['ids' => ['41', '73']]], $this->body(1));
        $this->assertSame(['add' => [['value' => $expectedRule . ' lang:nl', 'tag' => null]]], $this->body(2));
        $this->assertSame('POST', $this->requests[2]['request']->getMethod());
        $this->assertSame('application/json', $this->requests[2]['request']->getHeaderLine('Content-Type'));
        $this->assertSame('https://api.twitter.com/2/tweets/search/stream?expansions=author_id&tweet.fields=author_id', (string) $this->requests[3]['request']->getUri());
    }

    public static function filters(): array
    {
        return [
            'one keyword' => ['whenHears', 'php', '(php)'],
            'keyword alternatives' => ['whenHears', ['php', 'laravel'], '(php OR laravel)'],
            'one author' => ['whenTweets', 'spatie_be', '(from:spatie_be)'],
            'author alternatives' => ['whenTweets', ['spatie_be', 'freekmurze'], '(from:spatie_be OR from:freekmurze)'],
            'one bounding box' => ['whenFrom', [[-74, 40, -73, 41]], '(bounding_box:[-74 40 -73 41])'],
            'bounding box alternatives' => ['whenFrom', [[-74, 40, -73, 41], [-1, 50, 1, 51]], '(bounding_box:[-74 40 -73 41] OR bounding_box:[-1 50 1 51])'],
        ];
    }

    public function test_user_stream_delivers_posts_from_a_handle_not_activity_events(): void
    {
        $connection = $this->connection([
            new Response(200, [], '{}'),
            new Response(201, [], '{}'),
            $this->posts(),
        ]);
        $received = [];

        UserStream::create('spatie_be', $connection)
            ->onTweet(function (object $post) use (&$received) {
                $received[] = $post->data->text;
            })
            ->startListening();

        $this->assertSame(['Hello 🐘', 'Another post', 'Last post'], $received);
        $this->assertCount(3, $this->requests);
        $this->assertSame(['add' => [['value' => '(from:spatie_be)', 'tag' => null]]], $this->body(1));
    }

    public function test_filters_survive_restarting_the_listener(): void
    {
        $connection = $this->connection([
            new Response(200, [], '{}'), new Response(201, [], '{}'), $this->posts(),
            new Response(200, [], '{}'), new Response(201, [], '{}'), $this->posts(),
        ]);
        $received = [];
        $stream = PublicStream::create($connection)->whenHears('php', function (object $post) use (&$received) {
            $received[] = $post->data->id;
        });

        $stream->startListening((new FilteredStream())->withTweetLimit(1));
        $stream->startListening((new FilteredStream())->withTweetLimit(1));

        $this->assertSame(['234', '234'], $received);
        $this->assertSame(['add' => [['value' => '(php)', 'tag' => null]]], $this->body(1));
        $this->assertSame($this->body(1), $this->body(4));
    }

    public function test_chained_filters_are_combined_and_use_the_last_callback(): void
    {
        $connection = $this->connection([new Response(200, [], '{}'), new Response(201, [], '{}'), $this->posts()]);
        $received = [];

        PublicStream::create($connection)
            ->whenHears(['php', 'laravel'], function () {
                $this->fail('Only the last callback should run.');
            })
            ->whenTweets(['spatie_be', 'freekmurze'], function (object $post) use (&$received) {
                $received[] = $post->data->id;
            })
            ->setLocale('en')
            ->startListening((new FilteredStream())->withTweetLimit(1));

        $this->assertSame(['234'], $received);
        $this->assertSame(['add' => [['value' => '(php OR laravel) (from:spatie_be OR from:freekmurze) lang:en', 'tag' => null]]], $this->body(1));
    }

    public function test_api_errors_are_propagated_before_opening_a_stream(): void
    {
        $connection = $this->connection([new Response(401, [], '{"title":"Unauthorized","detail":"Invalid token"}')]);

        $this->expectException(TwitterException::class);
        $this->expectExceptionMessage('Unauthorized');

        PublicStream::create($connection)->whenHears('php', fn () => null)->startListening();
    }

    public function test_callback_errors_are_propagated_and_the_stream_is_closed(): void
    {
        $response = $this->posts();
        $connection = $this->connection([new Response(200, [], '{}'), new Response(201, [], '{}'), $response]);

        try {
            PublicStream::create($connection)->whenHears('php', function () {
                throw new RuntimeException('Callback failed');
            })->startListening();

            $this->fail('Expected the callback exception.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Callback failed', $exception->getMessage());
        }

        $this->assertFalse($response->getBody()->isReadable());
    }

    #[DataProvider('unconfiguredStreams')]
    public function test_a_callback_is_required_before_any_rule_changes(string $class): void
    {
        $stream = $class === PublicStream::class
            ? PublicStream::create('unused-token')
            : UserStream::create('spatie_be', 'unused-token');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Configure a filter and callback');

        $stream->startListening();
    }

    public static function unconfiguredStreams(): array
    {
        return [[PublicStream::class], [UserStream::class]];
    }

    #[DataProvider('emptyFilters')]
    public function test_empty_filters_are_rejected(string $method, string|array $filter): void
    {
        $this->expectException(InvalidArgumentException::class);

        PublicStream::create('unused-token')->{$method}($filter, fn () => null);
    }

    public static function emptyFilters(): array
    {
        return [['whenHears', []], ['whenHears', ''], ['whenTweets', []], ['whenTweets', ''], ['whenFrom', []]];
    }

    protected function connection(array $responses): TwitterConnection
    {
        $handler = HandlerStack::create(new MockHandler($responses));
        $handler->push(Middleware::history($this->requests));

        return (new TwitterConnection('unused-token'))->setClient(new Client(['handler' => $handler]));
    }

    protected function body(int $index): array
    {
        return json_decode((string) $this->requests[$index]['request']->getBody(), true, flags: JSON_THROW_ON_ERROR);
    }

    protected function posts(): Response
    {
        $body = Utils::streamFor("\r\n" . json_encode([
            'data' => ['id' => '234', 'text' => 'Hello 🐘', 'author_id' => '99'],
            'includes' => ['users' => [['id' => '99', 'username' => 'writer']]],
            'matching_rules' => [['id' => '88']],
        ], JSON_THROW_ON_ERROR) . "\r\n\r\n" . '{"data":{"id":"567","text":"Another post"}}' . "\r\n" . '{"data":{"id":"890","text":"Last post"}}' . "\r\n");
        $stream = FnStream::decorate($body, [
            'getMetadata' => fn (?string $key = null) => $key === 'wrapper_type' ? 'http' : $body->getMetadata($key),
        ]);

        return new Response(200, [], $stream);
    }
}
