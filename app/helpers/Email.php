<?php
require_once __DIR__ . '/../library/SwiftMailer/swift_required.php';

class Email
{
	protected $host = 'localhost';
	protected $port = 25;
	protected $username = '';
	protected $password = '';
	protected $ssl = null;
	protected $from = '';

	protected $transporter = null;

	function __construct($host, $port, $username, $password, $ssl, $from) {
		$this->host = $host;
		$this->port = $port;
		$this->username = $username;
		$this->password = $password;
		$this->ssl = ($ssl == true) ? 'ssl' : null;
		$this->from = $from;

		$this->transporter = $this->_constructTransporter();
	}

	protected function _constructTransporter()
	{
		if ($this->username != '') {
			$transporter = Swift_SmtpTransport::newInstance($this->host, $this->port, $this->ssl)
  				->setUsername($this->username)
				->setPassword($this->password);
		}
		else {
			$transporter = Swift_MailTransport::newInstance();
		}

		return $transporter;
	}

	public function sendEmail($content, $to, $subject)
	{
		$message = Swift_Message::newInstance()
			->setSubject($subject)
			->setFrom(array((string)$this->from => 'Project Manager'))
			->setTo($to)
			->addPart($content, 'text/html');

		$mailer = Swift_Mailer::newInstance($this->transporter);
		$result = $mailer->send($message);
	}

	public function sendWelcomeEmail($user, $password)
	{
		$view = new \Phalcon\Mvc\View();
        $view->setViewsDir('../app/email/');
        $view->setVar('CompanyName', Config::getValue('core/name'));
        $view->setVar('full_name', $user->full_name);
        $view->setVar('email' , $user->email);
        $view->setVar('password', $password);
        $view->start();
        $view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        $view->render('user', 'welcome');
        $view->finish();
        $content = $view->getContent();

        $this->sendEmail($content, $user->email, 'Welcome ' . $user->full_name . '!');
	}

	public function sendCommentEmail($comment)
	{
		$task = $comment->getTask();
		$commenter_name = $comment->getUser()->full_name;
		$project = $task->getProject();
		$subscribers = $task->getTaskUser();
		$message = $comment->comment;

		foreach($subscribers AS $subscriber) {
			$view = new \Phalcon\Mvc\View();
	        $view->setViewsDir('../app/email/');
	        $view->setVar('CompanyName', Config::getValue('core/name'));
	        $view->setVar('full_name', $subscriber->getUser()->full_name);
	        $view->setVar('commenter_name', $commenter_name);
	        $view->setVar('task', $task);
	        $view->setVar('project', $project);
	        $view->setVar('comment', $comment);
	        $view->start();
	        $view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
	        $view->render('comment', 'comment');
	        $view->finish();
	        $content = $view->getContent();

			$this->sendEmail($content, $subscriber->getUser()->email, 'A comment has been posted on a task you are participating!');
		}
	}

	public function sendAssignedEmail($task)
	{
		$project = $task->getProject();

		$view = new \Phalcon\Mvc\View();
        $view->setViewsDir('../app/email/');
        $view->setVar('CompanyName', Config::getValue('core/name'));
        $view->setVar('full_name', $task->getUser()->full_name);
        $view->setVar('task', $task);
        $view->setVar('project', $project);
        $view->start();
        $view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        $view->render('task', 'assigned');
        $view->finish();
        $content = $view->getContent();

		$this->sendEmail($content, $task->getUser()->email, 'A task has been assigned to you!');
	}

	public function sendTaskClosedEmail($task)
	{
		$project = $task->getProject();
		$subscribers = $task->getTaskUser();

		foreach($subscribers AS $subscriber) {
			$view = new \Phalcon\Mvc\View();
	        $view->setViewsDir('../app/email/');
	        $view->setVar('CompanyName', Config::getValue('core/name'));
	        $view->setVar('full_name', $subscriber->getUser()->full_name);
	        $view->setVar('task', $task);
	        $view->setVar('project', $project);
	        $view->start();
	        $view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
	        $view->render('task', 'closed');
	        $view->finish();
	        $content = $view->getContent();

	        $this->sendEmail($content, $subscriber->getUser()->email, 'A task you were participating has been closed!');
		}
	}
}
