<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Services\EventService;
use App\Mail\EventReminderMail;

class SendEventReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send';
    private $EventService;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder emails befre events.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(EventService $EventService)
    {
        $this->EventService = $EventService;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
      $now = Carbon::now();
      $notifications = $this->EventService->getAlertNotifications($now);

      foreach ($notifications as $dto) {
        Mail::to($dto->user_email)->send(new EventReminderMail($dto));
      }
      $this->info('Sent reminders.');
    }
}
