<?php

namespace Spatie\TwitterStreamingApi;

abstract class BaseStream
{
    protected PhirehoseWrapper $stream;

    public static function create(string $accessToken, string $accessSecret, string $consumerKey, string $consumerSecret)
    {
        /** @psalm-suppress TooManyArguments */
        return new static(...func_get_args());
    }

    public function startListening()
    {
        $this->stream->startListening();
    }

    protected function createStream(
        string $accessToken,
        string $accessSecret,
        string $consumerKey,
        string $consumerSecret,
        string $filter
    ): PhirehoseWrapper {
        return new PhirehoseWrapper(
            $accessToken,
            $accessSecret,
            $consumerKey,
            $consumerSecret,
            $filter
        );
    }
}
