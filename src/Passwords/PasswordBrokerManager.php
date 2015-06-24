<?php namespace Ollieread\Multiauth\Passwords;

use Illuminate\Mail\Mailer;

class PasswordBrokerManager
{

    protected $brokers = array();

    public function __construct(TokenRepositoryInterface $tokens, Mailer $mailer, $reminderViews, $providers)
    {
        foreach ($providers as $type => $provider) {
            $this->brokers[$type] = new PasswordBroker($type, $tokens, $provider, $mailer, $reminderViews[$type]);
        }
    }

    /**
     * @param $name
     * @param array $arguments
     * @return mixed
     */
    public function __call($name, $arguments = array())
    {
        if (array_key_exists($name, $this->brokers)) {
            return $this->brokers[$name];
        }
    }


}
