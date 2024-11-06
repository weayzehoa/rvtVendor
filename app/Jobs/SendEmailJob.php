<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Mail\SendEmailForQueuing;
use Mail;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $details;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email = new SendEmailForQueuing($this->details);
        $Mail = Mail::to($this->details['to']);
        !empty($this->details['cc']) ? $Mail = $Mail->cc($this->details['cc']) : '';
        !empty($this->details['bcc']) ? $Mail = $Mail->bcc($this->details['bcc']) : '';
        $Mail = $Mail->send($email);
    }
}
