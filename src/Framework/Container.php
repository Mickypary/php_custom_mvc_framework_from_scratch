<?php

declare(strict_types=1);

namespace Framework;

use ReflectionClass;
use Closure;
use ReflectionNamedType;
use Exception;

class Container
{
  private array $registry = [];

  // Note: CLosure is the class used to represent anonymous function type
  public function set(string $name, Closure $value): void
  {
    $this->registry[$name] = $value;
  }
  public function get(string $class_name): object
  {
    if (array_key_exists($class_name, $this->registry)) {
      return $this->registry[$class_name]();
    }
    $reflector = new ReflectionClass($class_name);
    $constructor = $reflector->getConstructor();

    $dependencies = [];

    if ($constructor === null) {
      return new $class_name;
    }

    foreach ($constructor->getParameters() as $parameter) {
      // $name = $parameter->getName();
      $type = $parameter->getType();

      if ($type === null) {
        throw new Exception("Constructor parameter '{$parameter->getName()}' in the $class_name class has no type declaration");
      }

      if (! ($type instanceof ReflectionNamedType)) {
        exit("Constructor parameter '{$parameter->getName()}' in the $class_name class is an invalid type: '{$parameter->getType()}' - only single named types supported");
      }

      if ($type->isBuiltin()) {
        exit("Unable to resolve constructor parameter '{$parameter->getName()}' of type '{$parameter->getType()}' in the $class_name class");
      }
      // var_dump($type);
      // Call the getObject recursively
      $dependencies[] = $this->get((string)$type);
      // print_r($dependencies);
    }


    // $controller_object = new $controller(new Viewer, new Product);
    // Using ... unpack the array of dependencies
    return new $class_name(...$dependencies);
  }
}
