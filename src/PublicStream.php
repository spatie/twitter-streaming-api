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
    public function whenHears($listenFor, callable $whenHears, $lang = false)
    {
        if (! is_array($listenFor)) {
            $listenFor = [$listenFor];
        }

        $this->stream->setTrack($listenFor);
        $this->stream->setLang($lang);
        $this->stream->performOnStreamActivity($whenHears);

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
}
