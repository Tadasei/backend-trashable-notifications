<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification;

use Illuminate\Database\Eloquent\{Builder, SoftDeletes};

class TrashableDatabaseNotification extends DatabaseNotification
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
	public function scopeForNotifiableTypes(
		Builder $query,
		array $notifiableTypes,
	): void {
		$query->whereIn("notifiable_type", $notifiableTypes);
	}

	/**
	 * Scope a query to only include notifications not for specific notifiable types.
	 */
	public function scopeNotForNotifiableTypes(
		Builder $query,
		array $notifiableTypes,
	): void {
		$query->whereNotIn("notifiable_type", $notifiableTypes);
	}

	/**
	 * Scope a query to only include notifications for specific contactable types.
	 */
	public function scopeForContactableTypes(
		Builder $query,
		array $contactableTypes,
	): void {
		$query->whereHasMorph(
			"notifiable",
			[ContactMethod::class],
			fn(
				Builder $contactMethodQuery,
			) => $contactMethodQuery->whereHasMorph(
				"contact",
				[Contact::class],
				fn(Builder $contactQuery) => $contactQuery->forContactableTypes(
					$contactableTypes,
				),
			),
		);
	}

	/**
	 * Scope a query to only include notifications not for specific contactable types.
	 */
	public function scopeNotForContactableTypes(
		Builder $query,
		array $contactableTypes,
	): void {
		$query->whereNotIn(
			"id",
			$this->select("id")->forContactableTypes($contactableTypes),
		);
	}

	/**
	 * Scope a query to only include notifications for specific contactables.
	 */
	public function scopeForContactables(
		Builder $query,
		string $contactableType,
		array|Builder $contactableIds,
	): void {
		$query->whereHasMorph(
			"notifiable",
			[ContactMethod::class],
			fn(
				Builder $contactMethodQuery,
			) => $contactMethodQuery->whereHasMorph(
				"contact",
				[Contact::class],
				fn(Builder $contactQuery) => $contactQuery->forContactables(
					$contactableType,
					$contactableIds,
				),
			),
		);
	}
}
