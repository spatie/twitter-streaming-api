<?php

namespace Spatie\TwitterStreamingApi;

use Phirehose;

class PublicStream extends BaseStream
{
    public function __construct(
        string $accessToken,
        string $accessSecret,
        string $consumerKey,
        string $consumerSecret
    ) {
        $this->stream = $this->createStream(
            $accessToken,
            $accessSecret,
            $consumerKey,
            $consumerSecret,
            Phirehose::METHOD_FILTER
        );
    }

    /**
     * @param string|array $listenFor
     * @param callable $whenHears
     * @param string $lang
     *
     * @return $this
     */
    public function whenHears($listenFor, callable $whenHears)
    {
        if (! is_array($listenFor)) {
            $listenFor = [$listenFor];
        }

        $this->stream->setTrack($listenFor);

        $this->stream->performOnStreamActivity($whenHears);

        return $this;
    }

    /**
     * Specify a set of bounding boxes to track as an array containing one or
     * more 4 element lon/lat pairs denoting `[<south-west point longitude>,
     * <south-west point latitude>, <north-east point longitude>,
     * <north-east point latitude>]`. Only tweets that are both created using
     * the Geotagging API and are placed from within one of the tracked bounding
     * boxes will be included in the stream. The user's location field is not
     * used to filter tweets.
     *
     * **Example:**
     *     PublicStream::create($accessToken, $accessTokenSecret, $consumerKey, $consumerSecret)
     *         ->whenFrom([
     *             [-122.75, 36.8, -121.75, 37.8], // San Francisco
     *             [-74, 40, -73, 41],             // New York
     *         ], function(array $tweet) {
     *             echo "{$tweet['user']['screen_name']} just tweeted {$tweet['text']} from SF or NYC";
     *         })->startListening();
     *
     * @param array    $boundingBoxes
     * @param callable $whenFrom
     * @return $this
     */
    public function whenFrom(array $boundingBoxes, callable $whenFrom)
    {
        $this->stream->setLocations($boundingBoxes);

        $this->stream->performOnStreamActivity($whenFrom);

        return $this;
    }

    /**
     * @param string|array $twitterUserIds
     * @param callable $whenTweets
     *
     * @return $this
     */
    public function whenTweets($twitterUserIds, callable $whenTweets)
    {
        if (! is_array($twitterUserIds)) {
            $twitterUserIds = [$twitterUserIds];
        }

        $this->stream->setFollow($twitterUserIds);

        $this->stream->performOnStreamActivity($whenTweets);

        return $this;
    }

    /**
     * Restricts tweets to the given language, given by an ISO 639-1 code
     * (http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes).
     *
     * @param string $lang
     *
     * @return $this
     */
    public function setLocale(string $lang)
    {
        $this->stream->setLang($lang);

        return $this;
    }

    /**
     * This method allows you to change the filter.
     * @param callable $checkFilterPredicates
     *
     * @return $this
     */
    public function checkFilterPredicates(callable $checkFilterPredicates)
    {
        $this->stream->setCheckFilterPredicates($checkFilterPredicates);

        return $this;
    }
}
