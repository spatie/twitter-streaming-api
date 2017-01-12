<?php

namespace Spatie\TwitterStreamingApi;

use Phirehose;

class UserStream extends BaseStream
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
            Phirehose::METHOD_USER
        );
    }

    /**
     * @param callable $onEvent
     *
     * @return $this
     */
    public function onEvent(callable $onEvent)
    {
        $this->stream->performOnStreamActivity($onEvent);

        return $this;
    }
}
