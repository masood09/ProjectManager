<?php

class Security extends Phalcon\Mvc\User\Plugin
{

    /**
     * @var Phalcon\Acl\Adapter\Memory
     */
    protected $_acl;

    public function __construct($dependencyInjector)
    {
        $this->_dependencyInjector = $dependencyInjector;
    }

    public function getAcl()
    {
        if (!$this->_acl) {

            $acl = new Phalcon\Acl\Adapter\Memory();

            $acl->setDefaultAction(Phalcon\Acl::DENY);

            //Register roles
            $roles = array(
                'admin' => new Phalcon\Acl\Role('Admin'),
                'developer' => new Phalcon\Acl\Role('Developer'),
                'client' => new Phalcon\Acl\Role('Client'),
                'guest' => new Phalcon\Acl\Role('Guest')
            );

            foreach($roles as $role){
                $acl->addRole($role);
            }

            // Resources to which only admins have access.
            $adminResources = array(

            );

            // Resources to which only developers have access.
            $developerResources = array(

            );

            // Resources to which only clients have access.
            $clientResources = array(

            );

            // Common resources to which all registered users have access (ie., admin, developers and clients)
            $userResources = array(
                'ajax' => array('dashboard'),
                'dashboard' => array('index'),
                'index' => array('index'),
                'project' => array('view', 'getusersajax'),
                'task' => array('updateajax'),
                'user' => array('logout'),
            );

            // Resources to which all have access (ie., both registered and not registered users).
            $publicResources = array(
                'user' => array('login'),
            );

            // Grant access to admin areas to admins.
            foreach ($adminResources AS $resource => $actions) {
                $acl->addResource(new Phalcon\Acl\Resource($resource), $actions);

                foreach($actions AS $action) {
                    $acl->allow('Admin', $resource, $action);
                }
            }

            // Grant access to developer areas to developers and admin.
            foreach ($developerResources AS $resource => $actions) {
                $acl->addResource(new Phalcon\Acl\Resource($resource), $actions);

                foreach($actions AS $action) {
                    $acl->allow('Admin', $resource, $action);
                    $acl->allow('Developer', $resource, $action);
                }
            }

            // Grant access to client areas to clients and admin.
            foreach ($clientResources AS $resource => $actions) {
                $acl->addResource(new Phalcon\Acl\Resource($resource), $actions);

                foreach($actions AS $action) {
                    $acl->allow('Admin', $resource, $action);
                    $acl->allow('Client', $resource, $action);
                }
            }

            // Grant access to user areas to all registered users.
            foreach ($userResources AS $resource => $actions) {
                $acl->addResource(new Phalcon\Acl\Resource($resource), $actions);

                foreach($actions AS $action) {
                    $acl->allow('Admin', $resource, $action);
                    $acl->allow('Developer', $resource, $action);
                    $acl->allow('Client', $resource, $action);
                }
            }

            // Grant access to public areas to all.
            foreach ($publicResources AS $resource => $actions) {
                $acl->addResource(new Phalcon\Acl\Resource($resource), $actions);
                $acl->allow('Admin', $resource, '*');
                $acl->allow('Developer', $resource, '*');
                $acl->allow('Client', $resource, '*');
                $acl->allow('Guest', $resource, '*');
            }

            $this->_acl = $acl;
        }
        return $this->_acl;
    }

    /**
     * This action is executed before execute any action in the application
     */
    public function beforeDispatch(Phalcon\Events\Event $event, Phalcon\Mvc\Dispatcher $dispatcher)
    {
        $user = null;
        $user_id = $this->session->get('user_id');

        if (!$user_id) {
            $role = 'Guest';
        } else {
            $user = User::findFirst('id = "' . $user_id . '"');

            if ($user) {
                if ($user->role_id == 1) {
                    $role = 'Admin';
                }
                else if ($user->role_id == 2) {
                    $role = 'Developer';
                }
                else if ($user->role_id == 3) {
                    $role = 'Client';
                }
                else {
                    $role = 'Guest';
                }
            }
            else {
                $role = 'Guest';
            }
        }

        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();

        $acl = $this->getAcl();

        $allowed = $acl->isAllowed($role, $controller, $action);

        if ($allowed != Phalcon\Acl::ALLOW) {
            if ($role == 'Guest') {
                $this->flashSession->error('Please login before you proceed.');

                $dispatcher->forward(
                    array(
                        'controller' => 'user',
                        'action' => 'login'
                    )
                );

                return false;
            }
            else {
                $this->flashSession->error('You do not have permission to access this area.');
                $dispatcher->forward(
                    array(
                        'controller' => 'index',
                        'action' => 'index'
                    )
                );

                return false;
            }
        }

        $this->view->setVar('currentUser', $user);
    }
}
