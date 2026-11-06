<?php

namespace Spatie\TwitterStreamingApi;

use Closure;
use Felix\TwitterStream\RuleBuilder;
use Felix\TwitterStream\RuleManager;
use Felix\TwitterStream\Streams\FilteredStream;
use Felix\TwitterStream\TwitterConnection;
use InvalidArgumentException;
use LogicException;

class PublicStream
{
    protected TwitterConnection $connection;

    protected ?Closure $onTweet = null;

    protected RuleBuilder $rule;

    public function __construct(string|TwitterConnection $bearerToken)
    {
        $this->connection = $bearerToken instanceof TwitterConnection
            ? $bearerToken
            : new TwitterConnection($bearerToken);

        $this->rule = new RuleBuilder();
    }

    public static function create(string|TwitterConnection $bearerToken): static
    {
        return new static($bearerToken);
    }

    public function whenHears(string|array $listenFor, callable $whenHears): self
    {
        if ($listenFor === []) {
            throw new InvalidArgumentException('Provide at least one keyword.');
        }

        if ($listenFor === '') {
            throw new InvalidArgumentException('Provide at least one keyword.');
        }

        $this->rule->group(fn (RuleBuilder $rule) => $rule->raw($listenFor));
        $this->onTweet = Closure::fromCallable($whenHears);

        return $this;
    }

    public function whenFrom(array $boundingBoxes, callable $whenFrom): self
    {
        if ($boundingBoxes === []) {
            throw new InvalidArgumentException('Provide at least one bounding box.');
        }

        $this->rule->group(function (RuleBuilder $rule) use ($boundingBoxes) {
            foreach (array_values($boundingBoxes) as $index => $box) {
                $index === 0
                    ? $rule->boundingBox(...$box)
                    : $rule->orBoundingBox(...$box);
            }
        });

        $this->onTweet = Closure::fromCallable($whenFrom);

        return $this;
    }

    public function whenTweets(string|array $twitterUsers, callable $whenTweets): self
    {
        if ($twitterUsers === []) {
            throw new InvalidArgumentException('Provide at least one author.');
        }

        if ($twitterUsers === '') {
            throw new InvalidArgumentException('Provide at least one author.');
        }

        $this->rule->group(function (RuleBuilder $rule) use ($twitterUsers) {
            foreach (array_values((array) $twitterUsers) as $index => $user) {
                $index === 0
                    ? $rule->from($user)
                    : $rule->orFrom($user);
            }
        });

        $this->onTweet = Closure::fromCallable($whenTweets);

        return $this;
    }

    public function setLocale(string $lang): self
    {
        $this->rule->lang($lang);

        return $this;
    }

    public function startListening(?FilteredStream $stream = null): void
    {
        if ($this->onTweet === null) {
            throw new LogicException('Configure a filter and callback before listening.');
        }

        $rules = new RuleManager($this->connection);
        $rule = (new RuleBuilder(operators: clone $this->rule->operators))->build();

        $rules->delete($rules->all());
        $rules->save($rule);

        ($stream ?? new FilteredStream())->listen($this->connection, $this->onTweet);
    }
}
