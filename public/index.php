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

$configFile = __DIR__ . '/../app/config/config.xml';

error_reporting(E_ALL);

if (!file_exists($configFile)) {
    require_once(__DIR__ . '/../install/install.php');
    die;
}

try {
    // Read the configuration
    $config = simplexml_load_file($configFile, NULL, LIBXML_NOCDATA);

    $loader = new \Phalcon\Loader();

    /**
     * We're a registering a set of directories
     */
    $loader->registerDirs(
        array(
            __DIR__ . '/../app/controllers/',
            __DIR__ . '/../app/library/',
            __DIR__ . '/../app/models/',
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

        $security = new Security($di);

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
    $di->set('url', function() {
        $url = new \Phalcon\Mvc\Url();
        $url->setBaseUri(Config::getValue('core/baseUrl'));

        return $url;
    });

    $di->set('view', function() {
        $view = new \Phalcon\Mvc\View();
        $view->setViewsDir(__DIR__ . '/../app/views/');

        return $view;
    });

    /**
     * Database connection is created based in the parameters defined in the configuration file
     */
    $di->set('db', function() use ($config) {
        return new \Phalcon\Db\Adapter\Pdo\Mysql(array(
            "host" => $config->database->host,
            "username" => $config->database->username,
            "password" => $config->database->password,
            "dbname" => $config->database->dbname
        ));
    });

    /**
     * If the configuration specify the use of metadata adapter use it or use memory otherwise
     */
    $di->set('modelsMetadata', function() use ($config) {
        if(isset($config->metadata)){
            $metaDataConfig = $config->metadata;
            $metadataAdapter = 'Phalcon\Mvc\Model\Metadata\\'.$metaDataConfig->adapter;

            return new $metadataAdapter();
        } else {
            return new Phalcon\Mvc\Model\Metadata\Memory();
        }
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

    /*
     * The directory in which we will save the uploads.
     */
    $di->set('UploadDir', function() {
        return __DIR__ . '/uploads/';
    });

    $di->set('uploadHelper', function() use ($di) {
        return new UploadHelper($di);
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
