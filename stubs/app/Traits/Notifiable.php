<?php

namespace App\Http\Traits;

use App\Models\DatabaseNotification;

trait Notifiable
{
	use \Illuminate\Notifications\Notifiable;

	private array $notificationChannels = ["database"];

	/**
	 * Get the entity's notifications.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\MorphMany
	 */
	public function notifications()
	{
		return $this->morphMany(
			DatabaseNotification::class,
			"notifiable"
		)->latest();
	}

	public function getNotificationChannels(): array
	{
		return $this->notificationChannels;
	}

	public function setNotificationChannels(array $channels): void
	{
		$this->notificationChannels = $channels;
	}

	public function notifiableClass(): string
	{
		return __CLASS__;
	}
}
