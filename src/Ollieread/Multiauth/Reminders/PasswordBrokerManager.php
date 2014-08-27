<?php namespace Ollieread\Multiauth\Reminders;

use Illuminate\Mail\Mailer;

class PasswordBrokerManager {
	
	protected $brokers = array();
	
	public function __construct(ReminderRepositoryInterface $reminders, Mailer $mailer, $reminderViews, $providers)
	{
		foreach($providers as $type => $provider) {
			$this->brokers[$type] = new PasswordBroker($type, $reminders, $provider, $mailer, $reminderViews[$type]);
		}
	}
	
	public function __call($name, $arguments = array()) {
		if(array_key_exists($name, $this->brokers)) {
			return $this->brokers[$name];
		}
	}
	
	
	
}
