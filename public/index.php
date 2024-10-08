<?php

/**
 * Front controller
 *
 * PHP version 7.0
 */

/**
 * Composer
 */
require dirname(__DIR__) . '/vendor/autoload.php';


/**
 * Error and Exception handling
 */
error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');

/**
 * Sessions
 */

 session_start();


/**
 * Routing
 */
$router = new Core\Router();

// Add the routes
$router->add('', ['controller' => 'Home', 'action' => 'index']);
$router->add('login', ['controller' => 'Login', 'action' => 'new']);
$router->add('logout', ['controller' => 'Login', 'action' => 'destroy']);
$router->add('password/reset/{token:[\da-f]+}', ['controller' => 'Password', 'action' => 'reset']);
$router->add('signup/activate/{token:[\da-f]+}', ['controller' => 'Signup', 'action' => 'activate']);
$router->add('{controller}/{action}');
$router->add('settings/restore-(income|expense|payment)-categories', [
  'controller' => 'Settings', 
  'action' => 'restoreCategoriesAction',
  'type' => '$1' // tutaj 1 odnosi się do pierwszej grupy z wyrażenia regularnego (income|expense|payment)
]);
$router->add('settings/delete-(income|expense|payment)-category', [
  'controller' => 'Settings', 
  'action' => 'deleteCategoryAction',
  'type' => '$1' // tutaj 1 odnosi się do pierwszej grupy z wyrażenia regularnego (income|expense|payment)
]);
    
$router->dispatch($_SERVER['QUERY_STRING']);
