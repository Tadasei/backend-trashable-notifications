<?php

namespace App\Rules;

use App\Models\User;
use Closure;

use Illuminate\Contracts\Validation\{DataAwareRule, ValidationRule};

class ExistingMorphId implements ValidationRule, DataAwareRule
{
	public function __construct(
		protected string $morphTypeInputName,
		protected Closure $getErrorMessage
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
		Closure $fail
	): void {
		if (key_exists($this->morphTypeInputName, $this->data)) {
			["exists" => $exists, "attributeName" => $attributeName] = match (
			$this->data[$this->morphTypeInputName]
			) {
				User::class => [
					"exists" => User::where("id", $value)->exists(),
					"attributeName" => __("User"),
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
						: ($this->getErrorMessage)($attributeName, $attribute)
				);
			}
		}
	}
}
