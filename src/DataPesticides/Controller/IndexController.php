<?php

namespace Neveldo\DataPesticides\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class IndexController
 * Handle application homepage
 * @package Neveldo\DataPesticides\Controller
 */
class IndexController extends Controller
{
    /**
     * Returns application homepage
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $response = new Response(
            $this->renderView('index/index.php', [
                'title' => 'Data-Pesticides : Data-visualisation sur les pesticides dans les eaux souterraines',
            ])
        );

        $response->setPublic();
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->headers->addCacheControlDirective('max-age', 0);

        return $response;
    }
}