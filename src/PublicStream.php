<?php

namespace Spatie\TwitterStreamingApi;

use Phirehose;

class PublicStream extends BaseStream
{
    public function __construct($accessToken, $accessSecret, $consumerKey, $consumerSecret)
    {


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
     *
     * @return $this
     */
    public function whenHears($listenFor, callable $whenHears)
    {
        if (!is_array($listenFor)) {
            $listenFor = [$listenFor];
        }

        $this->stream->setTrack($listenFor);

        $this->stream->performOnStreamActivity($whenHears);

        return $this;
    }
}
