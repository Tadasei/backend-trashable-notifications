<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\Notification;

use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\{
	Facades\DB,
	Facades\Notification as FacadesNotification,
	Collection,
};

class SendNotification implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	/**
	 * Create a new job instance.
	 */
	public function __construct(
		private Collection|Model $notifiables,
		private Notification $notification,
	) {
		$this->onQueue("notifications");
	}

	/**
	 * Execute the job.
	 */
	public function handle(): void
	{
		DB::transaction(function () {
			FacadesNotification::send($this->notifiables, $this->notification);
		});
	}
}
