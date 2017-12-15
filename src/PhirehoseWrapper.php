<?php

namespace Spatie\TwitterStreamingApi;

use Phirehose;
use OauthPhirehose;

class PhirehoseWrapper extends OauthPhirehose
{
    /** @var callable */
    protected $onStreamActivity;

    /** @var callable */
    protected $checkFilterPredicates;

    /**
     * @param string $accessToken
     * @param string $accessSecret
     * @param string $consumerKey
     * @param string $consumerSecret
     * @param string $method
     */
    public function __construct($accessToken, $accessSecret, $consumerKey, $consumerSecret, $method = Phirehose::METHOD_FILTER)
    {
        parent::__construct($accessToken, $accessSecret, $method);

        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;

        if ($method === Phirehose::METHOD_USER) {
            $this->URL_BASE = 'https://userstream.twitter.com/1.1/';
        }

        $this->onStreamActivity = function ($status) {
        };

        $this->checkFilterPredicates = function () {
        };
    }

    /**
     * @param mixed $status
     */
    public function enqueueStatus($status)
    {
        ($this->onStreamActivity)(json_decode($status, true));
    }

    public function performOnStreamActivity(callable $onStreamActivity)
    {
        $this->onStreamActivity = $onStreamActivity;
    }

    public function startListening()
    {
        $this->consume();
    }

    /**
     * @param callable $checkFilterPredicates
     */
    public function setCheckFilterPredicates(callable $checkFilterPredicates)
    {
        $this->checkFilterPredicates = $checkFilterPredicates;
    }

    public function checkFilterPredicates()
    {
        ($this->checkFilterPredicates)($this);
    }
}
