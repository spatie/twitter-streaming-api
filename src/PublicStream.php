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
    )
    {
        $this->stream = $this->createStream(
            $accessToken,
            $accessSecret,
            $consumerKey,
            $consumerSecret,
            Phirehose::METHOD_FILTER
        );
    }

    public function whenHears(string | array $listenFor, callable $whenHears): self
    {
        if (!is_array($listenFor)) {
            $listenFor = [$listenFor];
        }

        $this->stream->setTrack($listenFor);

        $this->stream->performOnStreamActivity($whenHears);

        return $this;
    }

    public function whenFrom(array $boundingBoxes, callable $whenFrom): self
    {
        $this->stream->setLocations($boundingBoxes);

        $this->stream->performOnStreamActivity($whenFrom);

        return $this;
    }

    public function whenTweets(string | array $twitterUserIds, callable $whenTweets): self
    {
        if (!is_array($twitterUserIds)) {
            $twitterUserIds = [$twitterUserIds];
        }

        $this->stream->setFollow($twitterUserIds);

        $this->stream->performOnStreamActivity($whenTweets);

        return $this;
    }

    public function setLocale(string $lang): self
    {
        $this->stream->setLang($lang);

        return $this;
    }

    public function checkFilterPredicates(callable $checkFilterPredicates): self
    {
        $this->stream->setCheckFilterPredicates($checkFilterPredicates);

        return $this;
    }
}
