<?php

namespace App\Providers;

use Illuminate\Routing\ResourceRegistrar as OriginalRegistrar;

class ResourceRegistrar extends OriginalRegistrar
{
    // add json to the array
    /**
     * The default actions for a resourceful controller.
     *
     * @var array
     */
    protected $resourceDefaults = ['json', 'index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];


    /**
     * Add the json method for a resourceful route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array   $options
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceJson($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/json';

        $action = $this->getResourceAction($name, $controller, 'json', $options);

        return $this->router->get($uri, $action);
    }
}
