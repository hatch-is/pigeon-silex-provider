<?php

namespace Pigeon;

use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Class PigeonServiceProvider
 *
 * @package Pigeon
 */
class PigeonServiceProvider implements ServiceProviderInterface
{
    /**
     * @param Application $app
     */
    public function register(Application $app)
    {
        $app['hatch-is.pigeon.processor'] = $app->share(
            function () use ($app) {
                return new Processor($app['hatch-is.pigeon.endpoint']);
            }
        );
    }

    /**
     * @param Application $app
     */
    public function boot(Application $app)
    {
        // TODO: Implement boot() method.
    }
}