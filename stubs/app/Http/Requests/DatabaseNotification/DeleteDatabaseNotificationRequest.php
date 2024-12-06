<?php

namespace App\Http\Requests\DatabaseNotification;

use App\Models\DatabaseNotification;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DeleteDatabaseNotificationRequest extends FormRequest
{
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
	 */
	public function rules(): array
	{
		return [
			"notifications" => ["required", "array"],
			"notifications.*" => [
				"distinct:strict",
				"string",
				Rule::exists(DatabaseNotification::class, "id"),
			],
		];
	}

	/**
	 * Get custom attributes for validator errors.
	 *
	 * @return array<string, string>
	 */
	public function attributes(): array
	{
		return [
			"notifications" => __("Notifications"),
		];
	}
}
