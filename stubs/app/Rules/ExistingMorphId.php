<?php

namespace App\Rules;

use Closure;

use App\Models\{Complaint, ContactMethod, Demand, FollowUp, User};
use Illuminate\Contracts\Validation\{DataAwareRule, ValidationRule};

class ExistingMorphId implements ValidationRule, DataAwareRule
{
	public function __construct(
		protected string $morphTypeInputName,
		protected Closure $getErrorMessage,
	) {
	}

	/**
	 * All of the data under validation.
	 *
	 * @var array<string, mixed>
	 */
	protected $data = [];

	// ...

	/**
	 * Set the data under validation.
	 *
	 * @param  array<string, mixed>  $data
	 */
	public function setData(array $data): static
	{
		$this->data = $data;

		return $this;
	}

	/**
	 * Run the validation rule.
	 *
	 * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
	 */
	public function validate(
		string $attribute,
		mixed $value,
		Closure $fail,
	): void {
		if (key_exists($this->morphTypeInputName, $this->data)) {
			["exists" => $exists, "attributeName" => $attributeName] = match (
				$this->data[$this->morphTypeInputName]
			) {
				Demand::class => [
					"exists" => Demand::where("id", $value)->exists(),
					"attributeName" => __("Application"),
				],
				Complaint::class => [
					"exists" => Demand::complaints()
						->where("id", $value)
						->exists(),
					"attributeName" => __("Complaint"),
				],
				ContactMethod::class => [
					"exists" => ContactMethod::where("id", $value)->exists(),
					"attributeName" => __("Contact method"),
				],
				User::class => [
					"exists" => User::where("id", $value)->exists(),
					"attributeName" => __("User"),
				],
				FollowUp::class => [
					"exists" => FollowUp::where("id", $value)->exists(),
					"attributeName" => __("Follow-up"),
				],
				default => [
					"exists" => false,
					"attributeName" => null,
				],
			};

			if (!$exists) {
				$fail(
					is_null($attributeName)
						? __("$attribute is invalid")
						: ($this->getErrorMessage)($attributeName, $attribute),
				);
			}
		}
	}
}
