# Changelog

All notable changes to `twitter-streaming-api` will be documented in this file

## 3.0.0 - 2026-11-06

- Require PHP 8.5+ and upgrade to `redwebcreation/twitter-stream-api` 2.2.1+, PHPUnit 13, and the latest PHP CS Fixer.
- Remove Psalm and the unused Laravel Ray development dependency.
- Use app Bearer Tokens without API key/secret arguments, the `Felix` client namespace, and `FilteredStream` options instead of `Sets`.
- Deliver v2 object envelopes to callbacks and rename `UserStream::onEvent()` to `onTweet()`; user streams filter public posts, not likes or follows.
- Fix OR alternatives for authors and bounding boxes, group keyword alternatives, and use the `lang` operator for locale filters.
- Preserve filters when restarting a listener and reject missing callbacks and empty filters before changing app rules.
- Replace the placeholder test with transport-isolated behavior tests; test stable/lowest dependencies on PHP 8.5/8.6, Linux and Windows, with a dedicated coverage job.
- Document breaking changes, app-wide rule replacement, access requirements, and the distinction from X Activity API subscriptions.

## 2.0.0 - 2021-05-17

- require PHP 8 + use improved dependancy (#30)

## 1.4.2 - 2020-12-11

- allow PHP 8

## 1.4.1 - 2018-04-04

- fix compatiblity with PHP 7.2 by requiring a specifc version of the phirehose dependency

## 1.4.0 - 2018-01-20

- add ability to track tweets based on location

## 1.2.0 - 2017-05-23

- add `setLocale`

## 1.1.0 - 2017-02-07

- add ability to follow a user's tweets using `setFollow()`

## 1.0.0 - 2017-01-15

- initial release
