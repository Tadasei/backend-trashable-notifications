<?php

namespace App\Traits;

use App\Models\File;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

use Illuminate\Support\{Facades\Storage, Arr};

trait InteractsWithFiles
{
	private function storeResourceFileables(
		Request $request,
		Model $resource,
		string $inputName,
		string $relation,
		string $storagePath
	): void {
		if ($request->has($inputName)) {
			foreach ($request[$inputName] as $uploadedFile) {
				$resource->{$relation}()->save(
					new File([
						"name" => $uploadedFile->getClientOriginalName(),
						"path" => $uploadedFile->storePublicly($storagePath),
						"mime_type" => $uploadedFile->getClientMimeType(),
						"size" => $uploadedFile->getSize(),
					])
				);
			}
		}
	}

	private function updateResourceFileables(
		Request $request,
		Model $resource,
		string $inputName,
		string $relation,
		string $storagePath
	): void {
		if (!$request->has($inputName) || empty($request[$inputName])) {
			[$ids, $paths] = Arr::divide(
				$resource[$relation]->pluck("path", "id")->toArray()
			);

			File::whereIn("id", $ids)->delete();

			Storage::delete($paths);
		} else {
			$fileablesToDelete = $resource[$relation]->filter(function (
				File $fileable
			) use ($request, $inputName) {
				return !in_array(
					$fileable->id,
					array_filter(
						$request[$inputName],
						fn($value) => is_numeric($value)
					)
				);
			});

			[$ids, $paths] = Arr::divide(
				$fileablesToDelete->pluck("path", "id")->toArray()
			);

			Storage::delete($paths);

			File::whereIn("id", $ids)->delete();

			$remainingFileables = $resource[$relation]->except($ids);

			$uploadedFilesInput = array_filter(
				$request[$inputName],
				fn($value) => !is_numeric($value)
			);

			foreach ($uploadedFilesInput as $uploadedFile) {
				$existingFileable = $remainingFileables->firstWhere(
					"name",
					$uploadedFile->getClientOriginalName()
				);

				if ($existingFileable) {
					Storage::delete($existingFileable->path);
					$existingFileable->delete();
				}

				$resource->{$relation}()->save(
					new File([
						"name" => $uploadedFile->getClientOriginalName(),
						"path" => $uploadedFile->storePublicly($storagePath),
						"mime_type" => $uploadedFile->getClientMimeType(),
						"size" => $uploadedFile->getSize(),
					])
				);
			}
		}
	}

	private function deleteResourceFileables(
		string $resourceClass,
		array $resourceIds
	): void {
		[$ids, $paths] = Arr::divide(
			File::where("fileable_type", $resourceClass)
				->whereIn("fileable_id", $resourceIds)
				->pluck("path", "id")
				->toArray()
		);

		File::whereIn("id", $ids)->delete();

		Storage::delete($paths);
	}
}
