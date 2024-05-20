<?php

namespace App\Http\Requests\TrashableDatabaseNotification;

use App\Models\TrashableDatabaseNotification;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeleteTrashableDatabaseNotificationRequest extends FormRequest
{
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
	 */
	public function rules(): array
	{
		return [
			"notifications" => ["required", "array"],
			"notifications.*" => [
				"distinct:strict",
				"string",
				Rule::exists(TrashableDatabaseNotification::class, "id"),
			],
		];
	}
}
