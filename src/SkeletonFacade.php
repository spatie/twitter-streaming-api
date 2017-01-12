<?php

namespace Spatie\TwitterStreamingApi;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Spatie\TwitterStreamingApi\TwitterStreamingApiClass
 */
class TwitterStreamingApiFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'skeleton';
    }
}
