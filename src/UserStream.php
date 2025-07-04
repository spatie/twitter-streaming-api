<?php

namespace Spatie\TwitterStreamingApi;

use RWC\TwitterStream\Rule;
use RWC\TwitterStream\RuleBuilder;
use RWC\TwitterStream\Sets;
use RWC\TwitterStream\TwitterStream;

class UserStream
{
    protected string $handle;
    protected TwitterStream $stream;
    /** @var callable */
    protected $onTweet;

    public function __construct(string $handle, string $bearerToken, string $apiKey, string $apiSecretKey)
    {
        $this->handle = $handle;
        $this->stream = new TwitterStream($bearerToken, $apiKey, $apiSecretKey);
    }

    public static function create(string $handle, string $bearerToken, string $apiKey, string $apiSecretKey): static
    {
        return new self($handle, $bearerToken, $apiKey, $apiSecretKey);
    }

    public function onEvent(callable $onTweet): static
    {
        $this->onTweet = $onTweet;

        return $this;
    }

    public function startListening(?Sets $sets = null): void
    {
        // Delete old rules
        Rule::deleteBulk(...Rule::all());
        RuleBuilder::create()
            ->from($this->handle)
            ->save();

        foreach ($this->stream->filteredTweets($sets) as $tweet) {
            call_user_func($this->onTweet, $tweet);
        }
    }
}
