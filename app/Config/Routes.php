<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->get('admin', 'Operateur::login');
$routes->get('admin/deconnexion', 'Operateur::logout');
$routes->get('connexion', 'Client::login');
$routes->post('connexion', 'Client::authenticate');
$routes->get('deconnexion', 'Client::logout');
$routes->get('client', 'Client::dashboard');
$routes->get('client/solde', 'Client::solde');
$routes->get('client/depot', 'Client::depot');
$routes->post('client/depot', 'Client::enregistrerDepot');
$routes->get('client/retrait', 'Client::retrait');
$routes->post('client/retrait', 'Client::enregistrerRetrait');
$routes->get('client/retrait/(:num)', 'Client::detailRetrait/$1');
$routes->get('client/transfert', 'Client::transfert');
$routes->post('client/transfert', 'Client::enregistrerTransfert');
$routes->get('client/transfert/(:num)', 'Client::detailTransfert/$1');
$routes->get('client/historique', 'Client::historique');

$routes->group('operateur', ['namespace' => 'App\Controllers'], static function ($routes) {
    $routes->get('/', 'Operateur::index');
    $routes->get('comptes', 'Operateur::comptes');
    $routes->get('depots', 'Operateur::depots');
    $routes->get('retraits', 'Operateur::retraits');
    $routes->get('transferts', 'Operateur::transferts');
    $routes->get('gains', 'Operateur::gains');

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
