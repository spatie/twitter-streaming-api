<?php

namespace Spatie\TwitterStreamingApi;

use OauthPhirehose;
use Phirehose;

class PhirehoseWrapper extends OauthPhirehose
{
    /** @var callable */
    protected $onStreamActivity;

    public function __construct($accessToken, $accessSecret, $consumerKey, $consumerSecret, $method = Phirehose::METHOD_FILTER)
    {
        parent::__construct($accessToken, $accessSecret, $consumerKey, $consumerKey);

        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;

        $this->onStreamActivity = function($status) {};
    }

    /**
     * Enqueue each status
     *
     * @param string $status
     */
    public function enqueueStatus($status)
    {
        ($this->onStreamActivity)($status);
    }

    public function performOnStreamActivity(callable $onStreamActivity)
    {
        $this->onStreamActivity = $onStreamActivity;
    }

    public function startListening()
    {
        $this->consume();
    }
}
