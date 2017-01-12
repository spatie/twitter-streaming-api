<?php

namespace Spatie\TwitterStreamingApi;

abstract class BaseStream
{
    /** @var \Spatie\TwitterStreamingApi\PhirehoseWrapper */
    protected $stream;

    protected function createStream(
        string $accessToken,
        string $accessSecret,
        string $consumerKey,
        string $consumerSecret,
        string $filter
    ) : PhirehoseWrapper {
        return new PhirehoseWrapper(
            $accessToken,
            $accessSecret,
            $consumerKey,
            $consumerSecret,
            $filter
        );
    }

    public static function create($accessToken, $accessSecret, $consumerKey, $consumerSecret)
    {
        return new static(...func_get_args());
    }

    public function startListening()
    {
        $this->stream->startListening();
    }
}
