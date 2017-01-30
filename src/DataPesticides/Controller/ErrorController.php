<?php

namespace Neveldo\DataPesticides\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ErrorController
 * Handle error pages
 * @package Neveldo\DataPesticides\Controller
 */
class ErrorController extends Controller
{

    /**
     * Returns application error page
     * @param \Exception $e
     * @param Request $request
     * @param $code
     * @return Response
     */
    public function errorAction(\Exception $e, Request $request, $code)
    {
        return new Response('Page non trouvée.');
    }
}