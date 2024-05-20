<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ArrayItem implements ValidationRule
{
	public function __construct(
		protected array $rules,
		protected array $messages = [],
		protected array $attributes = []
	) {
		//
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
		$validator = validator(
			$value,
			$this->rules,
			$this->messages,
			$this->attributes
		);

		if ($validator->fails()) {
			$errors = $validator->errors();

			foreach ($errors->keys() as $key) {
				foreach ($errors->get($key) as $message) {
					$fail("$attribute.$key", $message);
				}
			}
		}
	}
}
