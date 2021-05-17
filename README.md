# Easily work with the Twitter Streaming API

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/twitter-streaming-api.svg?style=flat-square)](https://packagist.org/packages/spatie/twitter-streaming-api)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![run-tests](https://github.com/spatie/twitter-streaming-api/actions/workflows/run-tests.yml/badge.svg)](https://github.com/spatie/twitter-streaming-api/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/twitter-streaming-api.svg?style=flat-square)](https://packagist.org/packages/spatie/twitter-streaming-api)

Twitter provides a streaming API with which you can do interesting things such as listening for tweets that contain
specific strings or actions a user might take (e.g. liking a tweet, following someone,...). This package makes it very
easy to work with the API.

Here's a quick example:

```php
PublicStream::create(
    $bearerToken,
    $apiKey,
    $apiSecretKey
)->whenHears('@spatie_be', function(array $tweet) {
    echo "We got mentioned by {$tweet['user']['screen_name']} who tweeted {$tweet['text']}";
})->startListening();
```

There's no polling involved. The package will keep an open https connection with Twitter, events will be delivered in
real time.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/twitter-streaming-api.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/twitter-streaming-api)

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

In order to use this package you'll need to get some credentials from Twitter. Head over to
the [Developer Portal on Twitter](https://developer.twitter.com/) to create an application.

Once you've created your application, click on the `Keys and tokens` tab to retrieve your `bearer_token`
, `api_key`, `api_secret_key`.
![Keys and tokens tab on Twitter](docs/tokens.png)

## Usage

Currently, this package works with the public stream and the user stream. Both the `PublicStream` and `UserStream`
classes provide a `startListening` function that kicks of the listening process. Unless you cancel it your PHP process
will execute that function forever. No code after the function will be run.

### The public stream

The public stream can be used to listen for specific words that are being tweeted, receive Tweets that are being sent
from specific locations or to follow one or more users tweets.

#### Listen for Tweets containing specific words

The first parameter of `whenHears` must be a string or an array containing the word or words you want to listen for. The
second parameter should be a callable that will be executed when one of your words is used on Twitter.

```php
PublicStream::create(
    $bearerToken,
    $apiKey,
    $apiSecretKey
)->whenHears('@spatie_be', function(array $tweet) {
    echo "We got mentioned by {$tweet['user']['screen_name']} who tweeted {$tweet['text']}";
})->startListening();
```

#### Listen for Tweets from specific locations

The first parameter of `whenFrom` must be an array containing one or more bounding boxes, each as an array of 4 element
lon/lat pairs (looking
like `[<south-west point longitude>, <south-west point latitude>, <north-east point longitude>,  <north-east point latitude>]`)
. The second parameter should be a callable that will be executed when a Tweet from one of your tracked locations is
being sent.

**Track all tweets from San Francisco or New York:**

```php
PublicStream::create(
    $bearerToken,
    $apiKey,
    $apiSecretKey
)->whenFrom([
    [-122.75, 36.8, -121.75, 37.8], // San Francisco
    [-74, 40, -73, 41],             // New York
], function(array $tweet) {
        echo "{$tweet['user']['screen_name']} just tweeted {$tweet['text']} from SF or NYC";
})->startListening();
```

**Track all tweets with a location (from all over the world):**

```php
PublicStream::create(
    $bearerToken,
    $apiKey,
    $apiSecretKey
)->whenFrom([
        [-180, -90, 180, 90] // Whole world
], function(array $tweet) {
    echo "{$tweet['user']['screen_name']} just tweeted {$tweet['text']} with a location attached";
})->startListening();
```

#### Listen for Tweets from specific users

The first parameter of `whenTweets` must be a string or an array containing the Twitter user ID or IDs you wish to
follow. The second parameter should be a callable that will be executed when one of your followed users tweets. Only
public information relating to the Twitter user will be available.

```php
PublicStream::create(
    $bearerToken,
    $apiKey,
    $apiSecretKey
)->whenTweets('92947501', function(array $tweet) {
    echo "{$tweet['user']['screen_name']} just tweeted {$tweet['text']}";
})->startListening();
```

### The user stream

```php
UserStream::create(
    'your_handle',
    $bearerToken,
    $apiKey,
    $apiSecretKey
)->onEvent(function(array $event) {
    if ($event['event'] === 'favorite') {
        echo "Our tweet {$event['target_object']['text']} got favorited by {$event['source']['screen_name']}";
    }
})->startListening();
```

## A word to the wise

These APIs work in realtime, so they could report a lot of activity. If you need to do some heavy work processing that
activity, it's best to put that work in a queue to keep your listening process fast.

If you need more advanced functionalities, consider checking
out [redwebcreation/twitter-streaming-api](https://github.com/redwebcreation/twitter-streaming-api).

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## About Spatie

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source
projects [on our website](https://spatie.be/opensource).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
