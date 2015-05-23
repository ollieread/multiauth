<?php namespace Ollieread\Multiauth\Passwords;

use Carbon\Carbon;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;


class DatabaseTokenRepository implements TokenRepositoryInterface
{

    /**
     * The database connection instance.
     *
     * @var \Illuminate\Database\Connection
     */
    protected $connection;

    /**
     * The reminder database table.
     *
     * @var string
     */
    protected $table;

    /**
     * The hashing key.
     *
     * @var string
     */
    protected $hashKey;

    /**
     * The number of seconds a reminder should last.
     *
     * @var int
     */
    protected $expires;

    /**
     * Create a new token repository instance.
     *
     * @param  \Illuminate\Database\ConnectionInterface $connection
     * @param  string $table
     * @param  string $hashKey
     * @param  int $expires
     */
    public function __construct(ConnectionInterface $connection, $table, $hashKey, $expires = 60)
    {
        $this->table = $table;
        $this->hashKey = $hashKey;
        $this->expires = $expires * 60;
        $this->connection = $connection;
    }

    /**
     * Create a new reminder record and token.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword $user
     * @param string $type
     * @return string
     */
    public function create(CanResetPasswordContract $user, $type)
    {
        $email = $user->getEmailForPasswordReset();

        $this->deleteExisting($user, $type);

        // We will create a new, random token for the user so that we can e-mail them
        // a safe link to the password reset form. Then we will insert a record in
        // the database so that we can verify the token within the actual reset.
        $token = $this->createNewToken($user);

        $this->getTable()->insert($this->getPayload($email, $token, $type));

        return $token;
    }

    /**
     * Delete all existing reset tokens from the database.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword $user
     * @param string $type
     * @return int
     */
    protected function deleteExisting(CanResetPasswordContract $user, $type)
    {
        return $this->getTable()->where('email', $user->getEmailForPasswordReset())->where('type', $type)->delete();
    }

    /**
     * Build the record payload for the table.
     *
     * @param string $email
     * @param string $token
     * @param string $type
     * @return array
     */
    protected function getPayload($email, $token, $type)
    {
        return array('type' => $type, 'email' => $email, 'token' => $token, 'created_at' => new Carbon);
    }

    /**
     * Determine if a token record exists and is valid.
     *
     * @param \Illuminate\Contracts\Auth\CanResetPassword $user
     * @param string $token
     * @param string $type
     * @return bool
     */
    public function exists(CanResetPasswordContract $user, $token, $type)
    {
        $email = $user->getEmailForPasswordReset();

        $token = (array)$this->getTable()->where('email', $email)->where('token', $token)->first();

        return $token && !$this->tokenExpired($token);
    }

    /**
     * Determine if the token has expired.
     *
     * @param  array $token
     * @return bool
     */
    protected function tokenExpired($token)
    {
        $timestamp = is_array($token['created_at']) && array_key_exists('date', $token['created_at']) ?
            $token['created_at']['date'] :
            $token['created_at'];

        $createdPlusHour = strtotime($timestamp) + $this->expires;

        return $createdPlusHour < $this->getCurrentTime();
    }

    /**
     * Get the current UNIX timestamp.
     *
     * @return int
     */
    protected function getCurrentTime()
    {
        return time();
    }

    /**
     * Delete a reminder record by token.
     *
     * @param string $token
     * @param string $type
     * @return void
     */
    public function delete($token, $type)
    {
        $this->getTable()->where('token', $token)->where('type', $type)->delete();
    }

    /**
     * Delete expired reminders.
     *
     * @return void
     */
    public function deleteExpired()
    {
        $expired = Carbon::now()->subSeconds($this->expires);

        $this->getTable()->where('created_at', '<', $expired)->delete();
    }

    /**
     * Create a new token for the user.
     *
     * @param \Illuminate\Contracts\Auth\CanResetPassword $user
     * @return string
     */
    public function createNewToken(CanResetPasswordContract $user)
    {
        return hash_hmac('sha256', str_random(40), $this->hashKey);
    }

    /**
     * Begin a new database query against the table.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function getTable()
    {
        return $this->connection->table($this->table);
    }

    /**
     * Get the database connection instance.
     *
     * @return \Illuminate\Database\Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

}