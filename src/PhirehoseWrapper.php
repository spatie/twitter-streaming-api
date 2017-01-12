<?php

namespace Spatie\TwitterStreamingApi;

use Phirehose;
use OauthPhirehose;

class PhirehoseWrapper extends OauthPhirehose
{
    /** @var callable */
    protected $onStreamActivity;

    public function __construct($accessToken, $accessSecret, $consumerKey, $consumerSecret, $method = Phirehose::METHOD_FILTER)
    {
        parent::__construct($accessToken, $accessSecret, $method);

        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;

        $this->onStreamActivity = function ($status) {
        };
    }
    
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
