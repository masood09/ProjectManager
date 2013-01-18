<?php
// Copyright (C) 2013 Masood Ahmed

// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.

// You should have received a copy of the GNU General Public License
// along with this program. If not, see <http://www.gnu.org/licenses/>.

$actionPath = str_replace('_url=', '', $_SERVER['QUERY_STRING']);
$actionPath = str_replace('&', '?', $actionPath);


$baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . str_replace($actionPath, '', $_SERVER['REQUEST_URI']) . '/';

error_reporting(E_ALL);

try {
    $loader = new \Phalcon\Loader();

    /**
     * We're a registering a set of directories
     */
    $loader->registerDirs(
        array(
            __DIR__ . '/controllers/',
            __DIR__ . '/../app/library/',
            __DIR__ . '/../app/helpers/',
        )
    )->register();

    /**
     * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
     */
    $di = new \Phalcon\DI\FactoryDefault();

    /**
     * We register the events manager
     */
    $di->set('dispatcher', function() use ($di) {
        $eventsManager = $di->getShared('eventsManager');

        $security = new InstallSecurity($di);

        /**
         * We listen for events in the dispatcher using the Security plugin
         */
        $eventsManager->attach('dispatch', $security);

        $dispatcher = new Phalcon\Mvc\Dispatcher();
        $dispatcher->setEventsManager($eventsManager);

        return $dispatcher;
    });

    /**
     * The URL component is used to generate all kind of urls in the application
     */
    $di->set('url', function() use ($baseUrl) {
        $url = new \Phalcon\Mvc\Url();
        $url->setBaseUri($baseUrl);

        return $url;
    });

    $di->set('view', function() {
        $view = new \Phalcon\Mvc\View();
        $view->setViewsDir(__DIR__ . '/../app/views/');

        return $view;
    });

    /**
     * Start the session the first time some component request the session service
     */
    $di->set('session', function() {
        $session = new Phalcon\Session\Adapter\Files();
        $session->start();

        return $session;
    });

    /**
     * Register the flash service with custom CSS classes
     */
    $di->set('flashSession', function() {
        $flashSession = new Phalcon\Flash\Session(array(
            'error' => 'alert alert-error',
            'success' => 'alert alert-success',
            'notice' => 'alert alert-info',
            'warning' => 'alert alert-info',
        ));

        return $flashSession;
    });

    /**
     * Application version.
     */
    $di->set('AppVersion', function() {
        $version = array(
            'major' => 1,
            'minor' => 0,
            'patch' => 0,
        );

        return $version;
    });

    $application = new \Phalcon\Mvc\Application();
    $application->setDI($di);
    echo $application->handle()->getContent();
}
catch (Phalcon\Exception $e) {
    echo $e->getMessage();
}
catch (PDOException $e) {
    echo $e->getMessage();
}
