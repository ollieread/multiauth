<?php namespace Ollieread\Multiauth\Passwords;

use Closure;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Passwords\PasswordBroker as OriginalPasswordBroker;
use Illuminate\Contracts\Auth\PasswordBroker as PasswordBrokerContract;

class PasswordBroker extends OriginalPasswordBroker
{

    protected $type;

    /**
     * @param $type
     * @param TokenRepositoryInterface $tokens
     * @param UserProvider $users
     * @param MailerContract $mailer
     * @param $emailView
     */
    public function __construct(
        $type,
        TokenRepositoryInterface $tokens,
        UserProvider $users,
        MailerContract $mailer,
        $emailView
    ) {
        $this->users = $users;
        $this->mailer = $mailer;
        $this->tokens = $tokens;
        $this->emailView = $emailView;
        $this->type = $type;
    }

    /**
     * Send a password reset link to a user.
     *
     * @param  array $credentials
     * @param  \Closure|null $callback
     * @return string
     */
    public function sendResetLink(array $credentials, Closure $callback = null)
    {
        // First we will check to see if we found a user at the given credentials and
        // if we did not we will redirect back to this current URI with a piece of
        // "flash" data in the session to indicate to the developers the errors.
        $user = $this->getUser($credentials);

        if (is_null($user)) {
            return PasswordBrokerContract::INVALID_USER;
        }

        // Once we have the reminder token, we are ready to send a message out to the
        // user with a link to reset their password. We will then redirect back to
        // the current URI having nothing set in the session to indicate errors.
        $token = $this->tokens->create($user, $this->type);

        $this->emailResetLink($user, $token, $callback);

        return PasswordBrokerContract::RESET_LINK_SENT;
    }

    /**
     * Send the password reset link via e-mail.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword $user
     * @param  string $token
     * @param  \Closure|null $callback
     * @return void
     */
    public function emailResetLink(CanResetPasswordContract $user, $token, Closure $callback = null)
    {
        // We will use the reminder view that was given to the broker to display the
        // password reminder e-mail. We'll pass a "token" variable into the views
        // so that it may be displayed for an user to click for password reset.
        $view = $this->emailView;
        $type = $this->type;

        return $this->mailer->send($view, compact('token', 'user', 'type'),
            function ($m) use ($user, $token, $type, $callback) {
                $m->to($user->getEmailForPasswordReset());

                if (!is_null($callback)) {
                    call_user_func($callback, $m, $user, $type, $token);
                }
            });
    }

    /**
     * Reset the password for the given token.
     *
     * @param  array $credentials
     * @param  Closure $callback
     * @return mixed
     */
    public function reset(array $credentials, Closure $callback)
    {
        // If the responses from the validate method is not a user instance, we will
        // assume that it is a redirect and simply return it from this method and
        // the user is properly redirected having an error message on the post.
        $user = $this->validateReset($credentials);

        if (!$user instanceof CanResetPasswordContract) {
            return $user;
        }

        $pass = $credentials['password'];

        // Once we have called this callback, we will remove this token row from the
        // table and return the response from this callback so the user gets sent
        // to the destination given by the developers from the callback return.
        call_user_func($callback, $user, $pass);

        $this->tokens->delete($credentials['token'], $this->type);

        return PasswordBrokerContract::PASSWORD_RESET;
    }

    /**
     * Validate a password reset for the given credentials.
     *
     * @param  array $credentials
     * @return \Illuminate\Contracts\Auth\CanResetPassword
     */
    protected function validateReset(array $credentials)
    {
        if (is_null($user = $this->getUser($credentials))) {
            return PasswordBrokerContract::INVALID_USER;
        }

        if (!$this->validateNewPassword($credentials)) {
            return PasswordBrokerContract::INVALID_PASSWORD;
        }

        if (!$this->tokens->exists($user, $credentials['token'], $this->type)) {
            return PasswordBrokerContract::INVALID_TOKEN;
        }

        return $user;
    }

}
