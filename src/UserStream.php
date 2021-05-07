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

    public function onEvent(callable $onEvent): self
    {
        $this->stream->performOnStreamActivity($onEvent);

        return $this;
    }
}
