<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('operateur', ['namespace' => 'App\Controllers'], static function ($routes) {
    $routes->get('/', 'Operateur::index');

    $routes->get('prefixes', 'Operateur::prefixes');
    $routes->get('prefixes/nouveau', 'Operateur::newPrefixe');
    $routes->post('prefixes', 'Operateur::createPrefixe');
    $routes->get('prefixes/(:num)/modifier', 'Operateur::editPrefixe/$1');
    $routes->post('prefixes/(:num)', 'Operateur::updatePrefixe/$1');
    $routes->post('prefixes/(:num)/supprimer', 'Operateur::deletePrefixe/$1');

    $routes->get('types', 'Operateur::types');
    $routes->post('types/(:num)/basculer', 'Operateur::toggleType/$1');

    $routes->get('baremes', 'Operateur::baremes');
    $routes->get('baremes/nouveau', 'Operateur::newBareme');
    $routes->post('baremes', 'Operateur::createBareme');
    $routes->get('baremes/(:num)/modifier', 'Operateur::editBareme/$1');
    $routes->post('baremes/(:num)', 'Operateur::updateBareme/$1');
    $routes->post('baremes/(:num)/supprimer', 'Operateur::deleteBareme/$1');
});
