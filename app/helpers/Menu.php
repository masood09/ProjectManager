<?php

class Menu extends Phalcon\Mvc\User\Component
{
	protected $active;
	protected $controllerName;
	protected $actionName;

	private $_headerMenu = array(
        'pull-left' => array(
            'project' => array(
            	'caption' => 'Projects',
            	'controller' => 'project',
            	'action' => 'index',
            	'menuIdent' => 'controller',
            ),
            'task' => array(
            	'caption' => 'Tasks',
            	'controller' => 'task',
            	'action' => 'index',
            	'menuIdent' => 'controller',
            ),
        ),
        'pull-right' => array(
        	'admin' => array(
        		'caption' => 'Admin',
        		'controller' => 'admin',
        		'action' => 'index',
        		'menuIdent' => 'controller',
        	),
            'login' => array(
                'caption' => 'Log In',
                'controller' => 'user',
                'action' => 'login',
                'menuIdent' => 'both',
            ),
            'my_account' => array(
            	'caption' => 'My Account',
            	'href' => '#myAccountEdit',
            	'extras' => 'role="button" data-toggle="modal"',
            ),
            'logout' => array(
            	'caption' => 'Log Out',
            	'controller' => 'user',
            	'action' => 'logout',
            	'menuIdent' => 'both',
            ),
        ),
    );

	protected function _getMenuItemClass($menu_item) {
		$menu = '<li>';

		if (isset($menu_item['menuIdent'])) {
			if ($menu_item['menuIdent'] === 'both') {
				if ($this->controllerName === $menu_item['controller'] && $this->actionName === $menu_item['action'] && !$this->active) {
					$menu = '<li class="active">';
					$this->active = true;
				}
				else {
					$menu = '<li>';
				}
			}
			else if ($menu_item['menuIdent'] === 'controller') {
				if ($this->controllerName === $menu_item['controller'] && !$this->active) {
					$menu = '<li class="active">';
					$this->active = true;
				}
				else {
					$menu = '<li>';
				}
			}
		}
		else {
			$menu = '<li>';
		}

		return $menu;
	}

	public function getNavMenu()
	{
		$menu = null;

		$session_id = $this->session->get('session_id');

		if (SessionHelper::isLoggedIn($session_id)) {
			unset($this->_headerMenu['pull-right']['login']);
		}
		else {
			unset($this->_headerMenu['pull-left']['project']);
			unset($this->_headerMenu['pull-left']['task']);
			unset($this->_headerMenu['pull-right']['my_account']);
			unset($this->_headerMenu['pull-right']['logout']);
		}

		if (!SessionHelper::isAdmin($session_id)) {
			unset($this->_headerMenu['pull-right']['admin']);
		}

		$this->controllerName = $this->view->getControllerName();
		$this->actionName = $this->view->getActionName();

		foreach ($this->_headerMenu as $position => $menu_items) {
            $this->active = false;
			$menu .= '<ul class="nav ' .  $position . '">';

				foreach ($menu_items as $menu_item) {
					$menu .= $this->_getMenuItemClass($menu_item);

					if (isset($menu_item['controller']) && isset($menu_item['action'])) {
						$menu .= Phalcon\Tag::linkTo($menu_item['controller'] . '/' . $menu_item['action'], $menu_item['caption']);
					}
					else {
						$menu .= '<a href="' . $menu_item['href'] . '" ' . $menu_item['extras'] . '>' . $menu_item['caption'] . '</a>';
					}

					$menu .= '</li>';
				}

			$menu .= '</ul>';
		}

		return $menu;
	}
}