<?php

$configFile = __DIR__ . '/../app/config/config.xml';

error_reporting(E_ALL);

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

    $di->set('AppName', function() {
        return Config::getValue('core/name');
    });

    /**
     * Register a user component
     */
    $di->set('menu', function() {
        return new Menu();
    });

    $di->set('Email', function() {
        return new Email(
            Config::getValue('email/host'),
            Config::getValue('email/port'),
            Config::getValue('email/username'),
            Config::getValue('email/password'),
            Config::getValue('email/ssl'),
            Config::getValue('core/email')
        );
    });

    include_once "../app/library/Markdown/Markdown.php";

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
