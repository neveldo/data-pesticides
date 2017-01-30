<?php

namespace Neveldo\DataPesticides\Controller;
use Pimple\Container;

/**
 * Class Controller
 * Base controller that all application controllers should inherit
 * @package Neveldo\DataPesticides\Controller
 */
class Controller
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * Controller constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Render a view
     * @param $view view path (starting from resources/views/ directory)
     * @param array $data array of data to pass to the view
     * @return string
     */
    public function renderView($view, $data = [])
    {
        ob_start();
        require $this->container['app.rootdir'] . '/resources/views/' . $view;
        return ob_get_clean();
    }
}