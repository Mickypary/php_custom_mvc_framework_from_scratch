<?php

declare(strict_types=1);

namespace Framework;

use PhpParser\Node\Stmt\Foreach_;
use ReflectionMethod;
// use App\Models\Product;
use ReflectionClass;

class Dispatcher
{
  public function __construct(private Router $router, private Container $container)
  {
    // Not needed since the same as been implicitly done inside the constructor args
    // $this->router = $router;
  }

  public function handle(string $path)
  {
    $params = $this->router->match($path);

    if ($params === false) {
      exit('No route matched');
    }

    $action = $this->getActionName($params);

    $controller = $this->getControllerName($params);
    // print_r($controller);

    // Before we moved the below functions to a separate method for processing so we can use recursive to capture dependencies that depend on other dependencies
    // $reflector = new ReflectionClass($controller);
    // $constructor = $reflector->getConstructor();

    // $dependencies = [];

    // if ($constructor !== null) {
    //   foreach ($constructor->getParameters() as $parameter) {
    //     // $name = $parameter->getName();
    //     $type = (string) $parameter->getType();
    //     // var_dump($type);
    //     // Call the getObject recursively
    //     $dependencies[] = new $type;
    //   }
    // }

    // // $controller_object = new $controller(new Viewer, new Product);
    // // Using ... unpack the array of dependencies
    // $controller_object =  new $controller(...$dependencies);
    // $args = $this->getActionArguments($controller, $action, $params);

    // // use spread operator to unpack the arrays
    // $controller_object->$action(...$args);




    // A getObject method was created to capture for dependencies of dependencies. The commented code above has been moved to the getObject method below
    $controller_object = $this->container->get($controller);

    $args = $this->getActionArguments($controller, $action, $params);

    // use spread operator to unpack the arrays
    $controller_object->$action(...$args);
  }

  private function getActionArguments(string $controller, string $action, array $params): array
  {
    $args = [];
    $method = new ReflectionMethod($controller, $action);
    foreach ($method->getParameters() as $parameter) {
      $name = $parameter->getName();
      $args[$name] = $params[$name];
    }

    return $args;
  }

  private function getControllerName(array $params): string
  {
    $controller = $params['controller'];
    $controller = str_replace("-", "", ucwords(strtolower($controller), "-"));

    $namespace = "App\Controllers";
    if (array_key_exists("namespace", $params)) {
      $namespace .= "\\" . $params['namespace'];
    }

    return $namespace . "\\" . $controller;
  }

  private function getActionName(array $params): string
  {
    $action = $params['action'];
    $action = lcfirst(str_replace("-", "", ucwords(strtolower($action), "-")));

    return $action;
  }
}
