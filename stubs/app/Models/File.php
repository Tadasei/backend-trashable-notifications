<?php

namespace App\Models;

use Illuminate\Database\Eloquent\{
	Factories\HasFactory,
	Relations\MorphTo,
	Model
};

class File extends Model
{
	use HasFactory;

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array<int, string>
	 */
	protected $fillable = ["name", "path", "mime_type", "size"];

	/**
	 * Get the parent fileable model.
	 */
	public function fileable(): MorphTo
	{
		return $this->morphTo();
	}
}
