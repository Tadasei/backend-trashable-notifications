<?php

namespace App\Http\Controllers;

use App\Jobs\SendNotification;
use App\Models\DatabaseNotification;

use App\Http\Requests\{
	DatabaseNotification\DeleteDatabaseNotificationRequest,
	SendNotificationRequest
};
use Illuminate\Http\{JsonResponse, Request, Response};
use Illuminate\Support\Facades\{DB, Gate};

class NotificationController extends Controller
{
	/**
	 * Return a listing of the authenticated user's notifications.
	 */
	public function index(Request $request): JsonResponse
	{
		Gate::authorize("viewAny", DatabaseNotification::class);

		return response()->json([
			"notifications" => $request->user()->notifications,
		]);
	}

	/**
	 * Generically send a notification.
	 */
	public function send(SendNotificationRequest $request): Response
	{
		Gate::authorize("store", [
			DatabaseNotification::class,
			$request->all(),
		]);

		$notification = new $request->type(...$request->params);

		if ($request->send_at) {
			$notification->delay($request->date("send_at"));
		}

		SendNotification::dispatch(
			$request->notifiables,
			$notification
		)->afterCommit();

		return response()->noContent();
	}

	/**
	 * Read the specified notification and redirect to the related resource.
	 */
	public function read(DatabaseNotification $notification): Response
	{
		Gate::authorize("update", $notification);

		DB::transaction(fn() => $notification->markAsRead());

		return response()->noContent();
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(
		DeleteDatabaseNotificationRequest $request
	): Response {
		DatabaseNotification::whereIn("id", $request->notifications)
			->get()
			->each(
				fn(DatabaseNotification $notification) => Gate::authorize(
					"forceDelete",
					$notification
				)
			);

		DB::transaction(
			fn() => DatabaseNotification::whereIn(
				"id",
				$request->notifications
			)->forceDelete()
		);

		return response()->noContent();
	}
}
