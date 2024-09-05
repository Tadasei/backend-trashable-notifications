<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{Builder, SoftDeletes};

class DatabaseNotification extends
	\Illuminate\Notifications\DatabaseNotification
{
	use SoftDeletes;

	/**
	 * Scope a query to only include notifications of specific types.
	 */
	public function scopeForTypes(Builder $query, array $types): void
	{
		$query->whereIn("type", $types);
	}

	/**
	 * Scope a query to only include notifications not of specific types.
	 */
	public function scopeNotForTypes(Builder $query, array $types): void
	{
		$query->whereNotIn("type", $types);
	}

	/**
	 * Scope a query to only include notifications for specific notifiable types.
	 */
	public function scopeNotifiableTypes(
		Builder $query,
		array $notifiableTypes
	): void {
		$query->whereIn("notifiable_type", $notifiableTypes);
	}

	/**
	 * Scope a query to only include notifications not for specific notifiable types.
	 */
	public function scopeNotNotifiableTypes(
		Builder $query,
		array $notifiableTypes
	): void {
		$query->whereNotIn("notifiable_type", $notifiableTypes);
	}
}
