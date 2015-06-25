<?php namespace Ollieread\Multiauth\Reminders;

use Closure;
use Illuminate\Mail\Mailer;
use Illuminate\Auth\UserProviderInterface;
use Illuminate\Auth\Reminders\RemindableInterface;
use Illuminate\Auth\Reminders\PasswordBroker as OriginalPasswordBroker;

class PasswordBroker extends OriginalPasswordBroker {
	
	protected $type;
	
	public function __construct($type,
								ReminderRepositoryInterface $reminders,
                                UserProviderInterface $users,
                                Mailer $mailer,
                                $reminderView)
	{
		$this->users = $users;
		$this->mailer = $mailer;
		$this->reminders = $reminders;
		$this->reminderView = $reminderView;
		$this->type = $type;
	}
	
	/**
	 * Send a password reminder to a user.
	 *
	 * @param  array    $credentials
	 * @param  Closure  $callback
	 * @return string
	 */
	public function remind(array $credentials, Closure $callback = null)
	{
		// First we will check to see if we found a user at the given credentials and
		// if we did not we will redirect back to this current URI with a piece of
		// "flash" data in the session to indicate to the developers the errors.
		$user = $this->getUser($credentials);

		if (is_null($user))
		{
			return self::INVALID_USER;
		}

		// Once we have the reminder token, we are ready to send a message out to the
		// user with a link to reset their password. We will then redirect back to
		// the current URI having nothing set in the session to indicate errors.
		$token = $this->reminders->create($user, $this->type);

		$this->sendReminder($user, $token, $callback);

		return self::REMINDER_SENT;
	}

	/**
	 * Send the password reminder e-mail.
	 *
	 * @param  \Illuminate\Auth\Reminders\RemindableInterface  $user
	 * @param  string   $token
	 * @param  Closure  $callback
	 * @return void
	 */
	public function sendReminder(RemindableInterface $user, $token, Closure $callback = null)
	{
		// We will use the reminder view that was given to the broker to display the
		// password reminder e-mail. We'll pass a "token" variable into the views
		// so that it may be displayed for an user to click for password reset.
		$view = $this->reminderView;
		$type = $this->type;

		return $this->mailer->send($view, compact('token', 'user', 'type'), function($m) use ($user, $token, $type, $callback)
		{
			$m->to($user->getReminderEmail());

			if ( ! is_null($callback)) call_user_func($callback, $m, $user, $type, $token);
		});
	}
	
	/**
	 * Reset the password for the given token.
	 *
	 * @param  array    $credentials
	 * @param  Closure  $callback
	 * @return mixed
	 */
	public function reset(array $credentials, Closure $callback)
	{
		// If the responses from the validate method is not a user instance, we will
		// assume that it is a redirect and simply return it from this method and
		// the user is properly redirected having an error message on the post.
		$user = $this->validateReset($credentials);

		if ( ! $user instanceof RemindableInterface)
		{
			return $user;
		}

		$pass = $credentials['password'];

		// Once we have called this callback, we will remove this token row from the
		// table and return the response from this callback so the user gets sent
		// to the destination given by the developers from the callback return.
		call_user_func($callback, $user, $pass);

		$this->reminders->delete($credentials['token'], $this->type);

		return self::PASSWORD_RESET;
	}
	
	/**
	 * Validate a password reset for the given credentials.
	 *
	 * @param  array  $credentials
	 * @return \Illuminate\Auth\Reminders\RemindableInterface
	 */
	protected function validateReset(array $credentials)
	{
		if (is_null($user = $this->getUser($credentials)))
		{
			return self::INVALID_USER;
		}

		if ( ! $this->validNewPasswords($credentials))
		{
			return self::INVALID_PASSWORD;
		}

		if ( ! $this->reminders->exists($user, $credentials['token'], $this->type))
		{
			return self::INVALID_TOKEN;
		}

		return $user;
	}

}
