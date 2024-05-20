<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use App\Rules\{ArrayItem, ExistingMorphId, HasConstructorParamKeys};

class SendNotificationRequest extends FormRequest
{
	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
	 */
	public function rules(): array
	{
		return [
			"type" => [
				"required",
				"string",
				// Rule::in([
				// 	ExampleNotification::class,
				// ]),
			],
			"notifiables" => ["sometimes", "array"],
			"notifiables.*" => [
				"bail",
				"required",
				"array:type,id",
				new ArrayItem([
					"type" => [
						"required",
						"string",
						// Rule::in([
						//     ExampleNotifiable::class,
						// ]),
					],
					"id" => [
						"required",
						"numeric",
						"integer",
						new ExistingMorphId(
							"type",
							fn($attributeName, $attribute) => __(
								"Cannot send a notification to a non existing :attribute",
								["attribute" => strtolower($attributeName)]
							)
						),
					],
				]),
			],
			"params" => ["sometimes", "array", new HasConstructorParamKeys()],
			"params.*" => [
				"bail",
				"required",
				"array:model,value",
				new ArrayItem([
					"model" => [
						"sometimes",
						"string",
						// Rule::in([
						// 	ExampleModel::class,
						// ]),
					],
					"value" => [
						"present",
						"nullable",
						new ExistingMorphId(
							"model",
							fn($attributeName, $attribute) => __(
								"$attribute is invalid"
							)
						),
					],
				]),
			],
			"send_at" => ["sometimes", "date"],
		];
	}

	/**
	 * Handle a passed validation attempt.
	 */
	protected function passedValidation(): void
	{
		$this->replace(
			$this->safe()
				->merge([
					"notifiables" => collect($this->notifiables)->map(
						fn($item) => resolve($item["type"])->find($item["id"])
					),
					"params" => collect($this->params)->map(
						fn($item) => match (
						!key_exists("model", $item) ? null : $item["model"]
						) {
							null => $item["value"],
							default => resolve($item["model"])->find(
								$item["value"]
							),
						}
					),
				])
				->all()
		);
	}
}
