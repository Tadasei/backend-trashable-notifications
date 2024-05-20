<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(["auth:sanctum"])->group(function () {
	Route::delete("/notifications", [
		NotificationController::class,
		"destroy",
	])->name("notifications.destroy");

	Route::post("/notifications", [
		NotificationController::class,
		"send",
	])->name("notifications.send");

	Route::patch("/notifications/{notification}", [
		NotificationController::class,
		"read",
	])->name("notifications.read");

	Route::apiResource("notifications", NotificationController::class)->only([
		"index",
	]);
});
