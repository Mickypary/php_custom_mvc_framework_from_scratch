<?php

namespace Framework;

// the routes in the routing table will consist of a string to represent the root path and an array to represent the parameters that match it i.e the controller and the action

class Router
{
  private array $routes = [];

  public function add(string $path, array $params = []): void
  {
    $this->routes[] = [
      'path' => $path,
      'params' => $params,
    ];
  }

  // match the path from the url to the list of routes in the routing table
  public function match(string $path): array|bool
  {
    $path = urldecode($path);

    $path = trim($path, "/");

    foreach ($this->routes as $key => $route) {

      // $pattern = "#^/(?<controller>[a-z]+)/(?<action>[a-z]+)$#";
      // echo $pattern, "\n", $route['path'], "\n";

      $pattern = $this->getPatternFromRoutePath($route['path']);
      // echo $pattern, "\n";

      // Process this if it matches url
      if (preg_match($pattern, $path, $matches)) {
        // $matches = array_filter($matches, "is_string", ARRAY_FILTER_USE_KEY);
        // OR the second array_filter explicitly specifying the callback
        $matches = array_filter($matches, function ($arg) {
          // return $arg == 'controller';
          return is_string($arg);
        }, ARRAY_FILTER_USE_KEY);
        // if there is no match with the url, the matches will be empty, so we merge with params
        $params = array_merge($matches, $route['params']);

        return $params;
      }
    }

    // For using routing table
    // foreach ($this->routes as $route) {
    //   if ($route['path'] === $path) {
    //     return $route['params'];
    //     // print_r($path);
    //   }
    // }

    return false;
  }

  // Shorthand
  // \d - [0-9]
  // \w - [A-Za-z0-9_]
  // \s - any whitespace character (space, tab etc)


  private function getPatternFromRoutePath(string $route_path): string
  {
    $route_path = trim($route_path, '/');
    $segments = explode("/", $route_path);
    // print_r($segments);
    $segments = array_map(function (string $segment): string {

      if (preg_match("#^\{([a-z][a-z0-9]*)\}$#", $segment, $matches)) {
        // print_r($matches);
        return "(?<" . $matches[1] . ">[^/]*)";
      }

      if (preg_match("#^\{([a-z][a-z0-9]*):(.+)\}$#", $segment, $matches)) {
        // print_r($matches);
        return "(?<" . $matches[1] . ">" . $matches[2] . ")";
      }

      // Note, this 3rd return is for those segments that only contains literal values and not matched variable segments
      return $segment;
    }, $segments);
    // print_r($segments);

    // Note, the u at the end of the expression is to match unicode character
    return "#^" . implode("/", $segments) . "$#iu";
  }
} // End Class
