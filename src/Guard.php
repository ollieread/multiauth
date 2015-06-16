<?php namespace Ollieread\Multiauth;

use Illuminate\Auth\Guard as OriginalGuard;
use Illuminate\Contracts\Auth\UserProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Illuminate\Support\Facades\Auth;

/**
 * Class Guard
 * @package Ollieread\Multiauth
 */
class Guard extends OriginalGuard
{

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $name;

    /**
     * @param \Illuminate\Contracts\Auth\UserProvider $provider
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param string $name
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function __construct(UserProvider $provider, SessionInterface $session, $name, Request $request = null)
    {
        parent::__construct($provider, $session, $request);

        $this->name = $name;
    }

    /**
     * Get a unique identifier for the auth session value.
     *
     * @return string
     */
    public function getName()
    {
        return 'login_' . $this->name . '_' . md5(get_class($this));
    }

    /**
     * Get the name of the cookie used to store the "recaller".
     *
     * @return string
     */
    public function getRecallerName()
    {
        return 'remember_' . $this->name . '_' . md5(get_class($this));
    }

    /**
     * Get the authenticated user instance.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function get()
    {
        return $this->user();
    }

    /**
     * Impersonate an authenticated user.
     *
     *
     * @param string $type
     * @param int $id
     * @param bool $remember
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function impersonate($type, $id, $remember = false)
    {
        if ($this->check()) {
            return Auth::$type()->loginUsingId($id, $remember);
        }
    }

}
