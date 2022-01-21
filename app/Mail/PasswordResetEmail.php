<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Lang;

class PasswordResetEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Password changed')
            ->markdown('vendor.notifications.reset-password-email', [
                'greeting' => sprintf('Hello %s!', $this->user->name),
				'introLines' => [
					Lang::get('You are receiving this email because we received a password reset request for your account.'),
					Lang::get('Your password has been reset to:'),
				],
				'password' => $this->user->original,
				'outroLines' => [
					Lang::get('If you did not request a password reset, Please update your password immediately.')
				]
            ]);
    }
}
