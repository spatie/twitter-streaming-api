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
     * @param string $lang
     * Restricts tweets to the given language, given by an ISO 639-1 code (http://en.wikipedia.org/wiki/List_of_ISO_639-1_codes).
     *
     * @return $this
     */
    public function setLocale($lang)
    {
        $this->stream->setLang($lang);

        return $this;
    }
}
