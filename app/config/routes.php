<?php

/**
 * Application outes
 */
return [
    "/" => ["Neveldo\DataPesticides\Controller\IndexController", "indexAction"],
    "/api/data/{action}" => ["Neveldo\DataPesticides\Controller\ApiController", "apiAction"],
];