<?php

namespace App\Policies;

use App\Models\{DatabaseNotification, User};

class DatabaseNotificationPolicy
{
	/**
	 * Determine whether the user can view any models.
	 */
	public function viewAny(User $user): bool
	{
		return true;
	}

	/**
	 * Determine whether the user can create models.
	 */
	public function create(User $user): bool
	{
		return true;
	}

	/**
	 * Determine whether the user can store the model.
	 */
	public function store(User $user, $context = null): bool
	{
		return true;
	}

	/**
	 * Determine whether the user can view the model.
	 */
	public function view(User $user, DatabaseNotification $notification): bool
	{
		return $notification->notifiable->is($user);
	}

	/**
	 * Determine whether the user can edit the model.
	 */
	public function edit(User $user, DatabaseNotification $notification): bool
	{
		return $this->view($user, $notification);
	}

	/**
	 * Determine whether the user can update the model.
	 */
	public function update(
		User $user,
		DatabaseNotification $notification,
		$context = null,
	): bool {
		return $this->edit($user, $notification);
	}

	/**
	 * Determine whether the user can delete the model.
	 */
	public function delete(
		User $user,
		DatabaseNotification $notification,
		$context = null,
	): bool {
		return $this->edit($user, $notification);
	}

	/**
	 * Determine whether the user can restore the model.
	 */
	public function restore(
		User $user,
		DatabaseNotification $notification,
	): bool {
		return true;
	}

	/**
	 * Determine whether the user can permanently delete the model.
	 */
	public function forceDelete(
		User $user,
		DatabaseNotification $notification,
	): bool {
		return $this->delete($user, $notification);
	}
}
