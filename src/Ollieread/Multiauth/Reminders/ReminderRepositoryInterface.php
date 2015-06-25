<?php namespace Ollieread\Multiauth\Reminders;

use Illuminate\Auth\Reminders\RemindableInterface;

interface ReminderRepositoryInterface {

	/**
	 * Create a new reminder record and token.
	 *
	 * @param  \Illuminate\Auth\Reminders\RemindableInterface  $user
	 * @return string
	 */
	public function create(RemindableInterface $user, $type);

	/**
	 * Determine if a reminder record exists and is valid.
	 *
	 * @param  \Illuminate\Auth\Reminders\RemindableInterface  $user
	 * @param  string  $token
	 * @return bool
	 */
	public function exists(RemindableInterface $user, $token, $type);

	/**
	 * Delete a reminder record by token.
	 *
	 * @param  string  $token
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