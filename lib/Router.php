<?php
/**
 * @author Evin Weissenberg
 * @todo not complete
 */

class Router {
    private $path;
    private $controller;
    private $action;
    static $instance;

    /**
     *
     */
    public function __construct() {
        $request = $_SERVER['REQUEST_URI'];
        $split = explode('/', trim($request, '/'));

        $this->controller = !empty($split[0]) ? ucfirst($split[0]) : 'Index';
        $this->action = !empty($split[1]) ? $split[1] : 'index';
    }

    /**
     * @param $registry
     */
    public function route($registry) {
        require_once('application/BaseController.php');
        $file = 'application/controllers/' . $this->controller . 'Controller.php';
        if (is_readable($file)) {
            include $file;
            $class = $this->controller . 'Controller';
        } else {
            include 'application/controllers/Error404Controller.php';
            $class = 'Error404Controller';
        }
        $controller = new $class($registry);

        if (is_callable(array($controller, $this->action)))
            $action = $this->action;
        else
            $action = 'index';
        $controller->$action();
    }

    function getLastPath($sUrl) {
        $sPath = parse_url($sUrl, PHP_URL_PATH); // parse URL and return only path component
        $aPath = explode('/', trim($sPath, '/')); // remove surrounding "/" and return parts into array
        return end($aPath); // last element of array
//        if (is_dir($sPath)) // if path points to dir
//        return current($aPath); // return last element of array
//        if (is_file($sPath)) // if path points to file
//        return prev($aPath); // return second to last element of array
//        return false; // or return false
    }
}