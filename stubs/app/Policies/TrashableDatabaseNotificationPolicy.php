<?php

namespace App\Policies;

use App\Models\{
	TrashableDatabaseNotification,
	User
};

class TrashableDatabaseNotificationPolicy
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
	public function view(
		User $user,
		TrashableDatabaseNotification $notification,
	): bool {
		return $notification->notifiable->is($user);
	}

	/**
	 * Determine whether the user can edit the model.
	 */
	public function edit(
		User $user,
		TrashableDatabaseNotification $notification,
	): bool {
		return $this->view($user, $notification);
	}

	/**
	 * Determine whether the user can update the model.
	 */
	public function update(
		User $user,
		TrashableDatabaseNotification $notification,
		$context = null,
	): bool {
		return $this->edit($user, $notification);
	}

	/**
	 * Determine whether the user can delete the model.
	 */
	public function delete(
		User $user,
		TrashableDatabaseNotification $notification,
		$context = null,
	): bool {
		return $this->edit($user, $notification);
	}

	/**
	 * Determine whether the user can delete the collection of models.
	 */
	public function deleteMany(
		User $user,
		array $notificationIds,
		$context = null,
	): bool {
		return $user
			->notifications()
			->pluck("id")
			->flip()
			->has($notificationIds);
	}

	/**
	 * Determine whether the user can restore the model.
	 */
	public function restore(
		User $user,
		TrashableDatabaseNotification $notification,
	): bool {
		return true;
	}

	/**
	 * Determine whether the user can permanently delete the model.
	 */
	public function forceDelete(
		User $user,
		TrashableDatabaseNotification $notification,
	): bool {
		return true;
	}
}
