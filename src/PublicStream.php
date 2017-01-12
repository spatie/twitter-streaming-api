<?php

namespace Spatie\TwitterStreamingApi;

use Phirehose;

class PublicStream extends TwitterStream
{
    public function __construct($accessToken, $accessSecret, $consumerKey, $consumerSecret)
    {
        parent::__construct(
            $accessToken,
            $accessSecret,
            $consumerKey,
            $consumerSecret,
            Phirehose::METHOD_FILTER);
    }
    
    /**
     * @param string|array $listenFor
     * @param callable $whenHears
     *
     * @return $this
     */
    public function whenHears($listenFor, callable $whenHears)
    {
        if (! is_array($listenFor)) {
            $listenFor = [$listenFor];
        }

        $this->setTrack($listenFor);

        $this->onStreamActivity = $whenHears;

        return $this;
    }
}
