# Easily work with the Twitter Streaming API

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/twitter-streaming-api.svg?style=flat-square)](https://packagist.org/packages/spatie/twitter-streaming-api)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/spatie/twitter-streaming-api/master.svg?style=flat-square)](https://travis-ci.org/spatie/twitter-streaming-api)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/eba105ae-0d82-4e89-bc5e-c87ba6dd1dd0.svg?style=flat-square)](https://insight.sensiolabs.com/projects/eba105ae-0d82-4e89-bc5e-c87ba6dd1dd0)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/twitter-streaming-api.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/twitter-streaming-api)
[![StyleCI](https://styleci.io/repos/78684837/shield?branch=master)](https://styleci.io/repos/78684837)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/twitter-streaming-api.svg?style=flat-square)](https://packagist.org/packages/spatie/twitter-streaming-api)

Twitter provides a streaming API with which you can do interesting things such as listening for tweets that contain specific strings or actions a user might take (e.g. liking a tweet, following someone,...). This package makes it very easy to work with the API.

Here's a quick example:

```php
PublicStream::create(
    $accessToken,
    $accessTokenSecret,
    $consumerKey,
    $consumerSecret
)->whenHears('@spatie_be', function(array $tweet) {
    echo "We got mentioned by {$tweet['user']['screen_name']} who tweeted {$tweet['text']}";
})->startListening();
```

 There's no polling involved. The package will keep an open https connection with Twitter, events will be delivered in real time.

Under the hood the [Phirehose package](https://github.com/fennb/phirehose) is used.

## Postcardware

You're free to use this package (it's [MIT-licensed](LICENSE.md)), but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

The best postcards will get published on the open source page on our website.

## Installation

You can install the package via composer:

``` bash
composer require spatie/twitter-streaming-api
```

## Getting credentials

In order to use this package you'll need to get some credentials from Twitter. Head over to the [Application management on Twitter](https://apps.twitter.com/) to create an application.

Once you've created your application, click on the `Keys and access tokens` tab to retrieve your `consumer_key`, `consumer_secret`, `access_token` and `access_token_secret`.

![Keys and access tokens tab on Twitter](https://spatie.github.io/twitter-streaming-api/images/twitter.jpg)

## Usage

Currently, this package works with the public stream and the user stream. Both the `PublicStream` and `UserStream` classes provide a `startListening` function that kicks of the listening process. Unless you cancel it your PHP process will execute that function forever. No code after the function will be run.

### The public stream

The public stream can be used to listen for specific words that are being tweeted or to follow one or more users tweets.

The first parameter of `whenHears` must be a string or an array containing the word or words you want to listen for. The second parameter should be a callable that will be executed when one of your words is used on Twitter.

```php
PublicStream::create(
    $accessToken,
    $accessTokenSecret,
    $consumerKey,
    $consumerSecret
)->whenHears('@spatie_be', function(array $tweet) {
    echo "We got mentioned by {$tweet['user']['screen_name']} who tweeted {$tweet['text']}";
})->startListening();
```

The first parameter of `whenTweets` must be a string or an array containing the Twitter user ID or ID's you wish to follow. The second parameter should be a callable that will be executed when one of your followed users tweets.  Only public information relating to the Twitter user will be available.

```php
PublicStream::create(
    $accessToken,
    $accessTokenSecret,
    $consumerKey,
    $consumerSecret
)->whenTweets('92947501', function(array $tweet) {
    echo "{$tweet['user']['screen_name']} just tweeted {$tweet['text']}";
})->startListening();
```

## Check filter predicates

In most cases your script will interacts with the Twitter streaming API as a daemon. If you want to change the filters while it is running you can pass a callable to `checkFilterPredicates`. That callable will be called every ~5 seconds.

Here's an example:

```php
PublicStream::create(
    $accessToken,
    $accessTokenSecret,
    $consumerKey,
    $consumerSecret
)->whenHears('@spatie_be', function(array $tweet) {
    echo "We got mentioned by {$tweet['user']['screen_name']} who tweeted {$tweet['text']}";
})->checkFilterPredicates(function($stream) {
    $trackIds = ExternalStorage::get('TwitterTrackIds');
    if ($trackIds != $stream->getTrack()) {
        $stream->setTrack($trackIds);
    }
})->startListening();
```

If you run in an external script something like

```php
ExternalStorage::set('TwitterTrackIds', ['@spatie_be', '@laravelphp'])
```

then the method in the example above will change the filter predicates and reconnect to the Twitter streaming API.

 `ExternalStorage::get/set` is just a dummy example. In real apps you'll probably use a file, in memory cache or db for this.

### The user stream

```php
UserStream::create(
    $accessToken,
    $accessTokenSecret,
    $consumerKey,
    $consumerSecret
)->onEvent(function(array $event) {
    if ($event['event'] === 'favorite') {
        echo "Our tweet {$event['target_object']['text']} got favorited by {$event['source']['screen_name']}";
    }
})->startListening();
```

## A word to the wise

These APIs work in realtime, so they could report a lot of activity. If you need to do some heavy work processing that activity it's best to put that work in a queue to keep your listening process fast.

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
Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
