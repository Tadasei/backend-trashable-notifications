<?php

namespace App\Http\Controllers;

use App\Jobs\SendNotification;
use Illuminate\Support\Facades\DB;

use App\Http\Requests\{
	TrashableDatabaseNotification\DeleteTrashableDatabaseNotificationRequest,
	SendNotificationRequest,
};
use App\Models\{Agent, TrashableDatabaseNotification};
use Illuminate\Http\{JsonResponse, Request, Response};

class NotificationController extends Controller
{
	/**
	 * Return a listing of the authenticated user's notifications.
	 */
	public function index(Request $request): JsonResponse
	{
		$this->authorize("viewAny", TrashableDatabaseNotification::class);

		return response()->json([
			"notifications" => $request->user()->notifications,
		]);
	}

	/**
	 * Generically send a notification.
	 */
	public function send(SendNotificationRequest $request): Response
	{
		$this->authorize("store", [
			TrashableDatabaseNotification::class,
			$request->all(),
		]);

		$notification = new $request->type(...$request->params);

		if ($request->send_at) {
			$notification->delay($request->date("send_at"));
		}

		SendNotification::dispatch(
			$request->notifiables,
			$notification,
		)->afterCommit();

		return response()->noContent();
	}

	/**
	 * Read the specified notification and redirect to the related resource.
	 */
	public function read(TrashableDatabaseNotification $notification): Response
	{
		$this->authorize("update", $notification);

		DB::transaction(fn() => $notification->markAsRead());

		return response()->noContent();
	}

	/**
	 * Remove the specified resource from storage.
	 */
	public function destroy(
		DeleteTrashableDatabaseNotificationRequest $request,
	): Response {
		$this->authorize("deleteMany", [
			TrashableDatabaseNotification::class,
			$request->notifications,
		]);

		DB::transaction(function () use ($request) {
			TrashableDatabaseNotification::notForContactableTypes([
				Agent::class,
			])
				->whereIn("id", $request->notifications)
				->forceDelete();

			TrashableDatabaseNotification::forContactableTypes([Agent::class])
				->whereIn("id", $request->notifications)
				->delete();
		});

		return response()->noContent();
	}
}
