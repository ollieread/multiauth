<?php namespace Ollieread\Multiauth\Passwords;

use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

interface TokenRepositoryInterface
{

    /**
     * Create a new token.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword $user
     * @param string $type
     * @return string
     */
    public function create(CanResetPasswordContract $user, $type);

    /**
     * Determine if a token record exists and is valid.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword $user
     * @param  string $token
     * @param $type
     * @return bool
     */
    public function exists(CanResetPasswordContract $user, $token, $type);

    /**
     * Delete a reminder record by token.
     *
     * @param  string $token
     * @param $type
     * @return void
     */
    public function delete($token, $type);

    /**
     * Delete expired reminders.
     *
     * @return void
     */
    public function deleteExpired();

}