<?php

namespace Spatie\TwitterStreamingApi;

use OauthPhirehose;
use Phirehose;

class TwitterStream extends OauthPhirehose
{
    /** @var callable */
    public $onStreamActivity;

    public function __construct($accessToken, $accessSecret, $consumerKey, $consumerSecret, $method = Phirehose::METHOD_FILTER)
    {
        parent::__construct($accessToken, $accessSecret, $method);

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

    public function startListening()
    {
        $this->consume();
    }
}
