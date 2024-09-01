<?php

declare(strict_types=1);


require __DIR__ . '/vendor/autoload.php';
// print_r($_GET);

// exit("Hello from index.php");

// use parse_url to split the url into diff elements
// $path = parse_url($_SERVER['REQUEST_URI']);
// print_r($path);
//output: Array ( [path] => /index/show [query] => page=2 )
// die;

// to also exclude the query string and leave only the path, we use an optional parameter called PHP_URL_PATH
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
// print_r($path);
// manual autoload with a callback
// spl_autoload_register(function (string $class_name) {
//   // convert using str_replace backslash to forward slash as backslash only works on windows but forward slash works on all OS. We use 2 backslash becos backslash is a special character. So by using 2 backslash, we escape one of them
//   require "src/" . str_replace("\\", "/", $class_name) . ".php";
// });


use Framework\Router;

$router = new Router;

// Add routes
$router->add("/admin/{controller}/{action}", ["namespace" => "Admin"]);
$router->add("/{title}/{id:\d+}/{page:\d+}", ["controller" => "products", "action" => "showPage"]);
$router->add("/product/{slug:[\w-]+}", ["controller" => "products", "action" => "show"]);
$router->add("/{controller}/{id:\d+}/{action}");
$router->add('/home/index', ["controller" => "home", "action" => "index"]);
$router->add('/products', ["controller" => "products", "action" => "index"]);
$router->add('/', ["controller" => "home", "action" => "index"]);
$router->add("/{controller}/{action}");

$container = new Framework\Container;

// $database = new App\Database("localhost", "product_db", "root", "");
// binding service container to the database class
// $container->set(App\Database::class, $database);

// Binding the way above will just create the object and push into the service container but this is not good as it just creates the objects even when its not needed. So we will use anonymous function as below
$container->set(App\Database::class, function () {
  return new App\Database("localhost", "product_db", "root", "");
});

$dispatcher = new Framework\Dispatcher($router, $container);

$dispatcher->handle($path);
