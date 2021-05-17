<?php

namespace Spatie\TwitterStreamingApi;

use RWC\TwitterStream\Rule;
use RWC\TwitterStream\RuleBuilder;
use RWC\TwitterStream\Sets;
use RWC\TwitterStream\Support\Arr;
use RWC\TwitterStream\TwitterStream;

class PublicStream
{
    protected TwitterStream $stream;

    /** @var callable */
    protected $onTweet;
    protected RuleBuilder $rule;

    public function __construct(string $bearerToken, string $apiKey, string $apiSecretKey)
    {
        $this->stream = new TwitterStream($bearerToken, $apiKey, $apiSecretKey);
        $this->rule = RuleBuilder::create();
    }

    public static function create(string $bearerToken, string $apiKey, string $apiSecretKey): static
    {
        return new self($bearerToken, $apiKey, $apiSecretKey);
    }

    public function whenHears(string | array $listenFor, callable $whenHears): self
    {
        $this->rule->raw(array_reduce(Arr::wrap($listenFor), static function ($_, $listener) {
            if (empty($_)) {
                return $listener;
            }

            return $_ . ' OR ' . $listener;
        }));
        $this->onTweet = $whenHears;

        return $this;
    }

    public function whenFrom(array $boundingBoxes, callable $whenFrom): self
    {
        $this->rule->boundingBox($boundingBoxes);
        $this->onTweet = $whenFrom;

        return $this;
    }

    public function whenTweets(string | array $twitterUserIds, callable $whenTweets): self
    {
        $this->rule->from($twitterUserIds);
        $this->onTweet = $whenTweets;

        return $this;
    }

    public function setLocale(string $lang): self
    {
        $this->rule->locale($lang);

        return $this;
    }

    public function startListening(Sets $sets = null): void
    {
        // Delete old rules
        Rule::deleteBulk(...Rule::all());
        $this->rule->save();

        foreach ($this->stream->filteredTweets($sets) as $tweet) {
            if (is_null($tweet)) {
                continue;
            }

            call_user_func($this->onTweet, $tweet);
        }
    }
}
