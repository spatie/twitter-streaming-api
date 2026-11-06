# Easily work with the Twitter Streaming API

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/twitter-streaming-api.svg?style=flat-square)](https://packagist.org/packages/spatie/twitter-streaming-api)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![run-tests](https://github.com/spatie/twitter-streaming-api/actions/workflows/run-tests.yml/badge.svg)](https://github.com/spatie/twitter-streaming-api/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/twitter-streaming-api.svg?style=flat-square)](https://packagist.org/packages/spatie/twitter-streaming-api)

Listen for public posts matching keywords, authors, languages, or bounding boxes using the X API v2
[Filtered Stream](https://docs.x.com/x-api/posts/filtered-stream/introduction).

This package does **not** subscribe to likes, follows, private activity, or legacy user events. Those require a separate
[X Activity API](https://docs.x.com/x-api/activity/introduction) integration with its own subscriptions and authentication.

Requires **PHP 8.5+**. CI exercises PHP 8.5 and PHP 8.6 development builds on Linux and Windows.

```php
use Spatie\TwitterStreamingApi\PublicStream;

PublicStream::create($bearerToken)
    ->whenHears('@spatie_be', function (object $message) {
        if (isset($message->data)) {
            echo $message->data->text;
        }
    })
    ->startListening();
```

There is no polling. The client reads a persistent HTTPS stream until it closes or your callback throws. Reconnection,
backoff, application supervision, and fast callback processing are the caller's responsibility.

## Support us

[<img src="https://spatie.be/github-ad-image/twitter-streaming-api.jpg" width="419px" />](https://spatie.be/github-ad-click/twitter-streaming-api)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can
support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.
You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards
on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Postcardware

You're free to use this package (it's [MIT-licensed](LICENSE.md)), but if it makes it to your production environment we
highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Kruikstraat 22, 2018 Antwerp, Belgium.

The best postcards will get published on the open source page on our website.

## Installation

You can install the package via composer:

``` bash
composer require spatie/twitter-streaming-api
```

## Getting credentials

Create an app in the [X Developer Console](https://developer.x.com/) and obtain its app Bearer Token. Your account must
have access to Filtered Stream and the operators you use. Access, billing, rule limits, and geo restrictions are controlled
by X; installing this package does not grant them. No API key or API secret argument is needed for these requests.

## Usage

**Rule ownership:** `startListening()` deletes **all existing filtered-stream rules for the app**, then adds the configured
rule before connecting. Use a dedicated app; do not share these rules with other consumers. Starting again replaces the
rules again. API and callback exceptions propagate to the caller.

Callbacks receive the upstream client's decoded **object envelope**, not a legacy tweet array:

```php
$message->data->id;
$message->data->text;
$message->includes;       // Only when returned by the API.
$message->matching_rules;
```

Keep-alive blank lines are not delivered to callbacks. Handle envelopes without `data`, including any streamed error
information your application receives. User details are not automatically hydrated at `$tweet['user']`.

### Keywords and languages

`whenHears` accepts a keyword/rule string or an array of alternatives. Arrays are grouped with OR, so the language filter
applies to every alternative. Different filter methods chained together are combined with AND; the last callback is used.

```php
PublicStream::create($bearerToken)
    ->whenHears(['php', 'laravel'], function (object $message) {
        // Process $message->data when present.
    })
    ->setLocale('en')
    ->startListening();
```

This submits `(php OR laravel) lang:en`. Raw strings use X's rule syntax; consult the
[rule-building guide](https://docs.x.com/x-api/posts/filtered-stream/integrate/build-a-rule) for quoting and operators.

### Posts by authors

`whenTweets` accepts an author handle or an array of alternative handles, without `@`. For example:

```php
PublicStream::create($bearerToken)
    ->whenTweets(['spatie_be', 'freekmurze'], function (object $message) {
        // Matches either author, not both simultaneously.
    })
    ->startListening();
```

`UserStream` is a convenience wrapper for a single `from:handle` post filter, **not an activity stream**:

```php
use Spatie\TwitterStreamingApi\UserStream;

UserStream::create('spatie_be', $bearerToken)
    ->onTweet(function (object $message) {
        // Public posts authored by spatie_be, not likes or follows.
    })
    ->startListening();
```

### Locations

`whenFrom` accepts one or more `[west longitude, south latitude, east longitude, north latitude]` boxes. Multiple boxes
are grouped with OR. X validates operator access and permitted box dimensions; a whole-world box is not promised.

```php
PublicStream::create($bearerToken)
    ->whenFrom([[-74, 40, -73, 41]], function (object $message) {
        // Process matching geolocated posts.
    })
    ->startListening();
```

### Stream options

Pass a configured `Felix\TwitterStream\Streams\FilteredStream` to `startListening()` for fields, expansions, or a post limit:

```php
use Felix\TwitterStream\Streams\FilteredStream;

$options = (new FilteredStream())
    ->fields(['tweet' => ['author_id']])
    ->expansions('author_id')
    ->withTweetLimit(100);

$publicStream->startListening($options);
```

Both factories also accept a configured `Felix\TwitterStream\TwitterConnection` instead of a token, for example to use
its `setClient()` transport configuration. A custom HTTP client must supply its own authentication headers.

## Upgrading from 2.x

- PHP 8.0–8.4 and the 0.x streaming client are no longer supported. The client is now `redwebcreation/twitter-stream-api` 2.2+.
- Pass only the Bearer Token (or a `TwitterConnection`) to `PublicStream::create()`. Pass a handle and token to `UserStream::create()`.
- Use object envelopes (`$message->data->text`) instead of legacy tweet arrays or top-level `text`/`user` fields.
- Rename `UserStream::onEvent()` to `onTweet()`. The old likes/follows example was never implemented by the filtered-post path; no legacy event translation is provided.
- Replace `RWC\TwitterStream\Sets` with a configured `Felix\TwitterStream\Streams\FilteredStream`.
- Author arrays now use OR, bounding boxes use the 2.2 builder signature, and `setLocale()` uses the `lang` operator.

## Testing and limitations

```bash
composer test
composer test-lint
composer test-coverage # Requires a coverage driver such as PCOV or Xdebug.
```

The suite exercises real rule generation, HTTP request bodies, stream parsing, payload delivery, limits, restart behavior,
and error propagation using a mocked HTTP transport. It makes no authenticated X calls and proves neither account
entitlement nor live delivery. The latest upstream client (2.2.1) currently emits a PHP implicit-nullability deprecation
from `RuleManager::save()` on PHP 8.5/8.6. The lowest-dependency resolution also emits a Guzzle promises class-name
deprecation on PHP 8.6. These remain visible in test output, not suppressed.

For lower-level functionality, see [the upstream client](https://github.com/felixdorn/twitter-stream-api).

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security

If you've found a bug regarding security please mail [security@spatie.be](mailto:security@spatie.be) instead of using the issue tracker.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## About Spatie

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source
projects [on our website](https://spatie.be/opensource).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
