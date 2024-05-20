<?php

namespace App\Rules;

use Closure;
use ReflectionClass;

use Illuminate\Contracts\Validation\{DataAwareRule, ValidationRule};

class HasConstructorParamKeys implements ValidationRule, DataAwareRule
{
	/**
	 * All of the data under validation.
	 *
	 * @var array<string, mixed>
	 */
	protected $data = [];

	// ...

	public function __construct(protected string $typeInputName = "type")
	{
	}

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
		if (
			array_diff(
				array_keys($value),
				array_column(
					(new ReflectionClass($this->data[$this->typeInputName]))
						->getConstructor()
						->getParameters(),
					"name",
				),
			)
		) {
			$fail(__("$attribute has invalid keys"));
		}
	}
}
