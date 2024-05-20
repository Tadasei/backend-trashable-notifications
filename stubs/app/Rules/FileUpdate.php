<?php

namespace App\Rules;

use App\Models\File;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class FileUpdate implements ValidationRule
{
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
		if (
			!(
				$value instanceof UploadedFile ||
				(is_numeric($value) && File::where("id", $value)->exists())
			)
		) {
			$fail(__("Invalid $attribute"));
		}
	}
}
