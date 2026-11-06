<?php

namespace Spatie\TwitterStreamingApi;

use Felix\TwitterStream\Streams\FilteredStream;
use Felix\TwitterStream\TwitterConnection;

class UserStream
{
    protected string $handle;

    protected PublicStream $stream;

    public function __construct(string $handle, string|TwitterConnection $bearerToken)
    {
        $this->handle = $handle;
        $this->stream = new PublicStream($bearerToken);
    }

    public static function create(string $handle, string|TwitterConnection $bearerToken): static
    {
        return new static($handle, $bearerToken);
    }

    public function onTweet(callable $onTweet): static
    {
        $this->stream->whenTweets($this->handle, $onTweet);

        return $this;
    }

    public function startListening(?FilteredStream $stream = null): void
    {
        $this->stream->startListening($stream);
    }
}
