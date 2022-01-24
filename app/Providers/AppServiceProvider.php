<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
		ResetPassword::toMailUsing(function ($notifiable, $url) {            
			$minute = 10;
			$verifyUrl = URL::temporarySignedRoute(
                'reset-password',
                Carbon::now()->addMinutes(Config::get('auth.verification.expire', $minute)),
                [
                    'email' => $notifiable->getEmailForPasswordReset(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );
			return (new MailMessage)
				->greeting(sprintf('Hello %s!', $notifiable->name))
				->subject(Lang::get('Reset Password Notification'))
				->line(Lang::get('You are receiving this email because we received a password reset request for your account.'))
				->action(Lang::get('Reset Password'), $verifyUrl)
				->line(Lang::get('This password reset link will expire in :count minutes.', ['count' => $minute]))
				->line(Lang::get('If you did not request a password reset, no further action is required.'));
		});
		VerifyEmail::toMailUsing(function ($notifiable, $url) {
            $verifyUrl = URL::temporarySignedRoute(
                'verification.verify',
                Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );
			return (new MailMessage)
                ->greeting(sprintf('Hello %s!', $notifiable->name))
                ->subject(Lang::get('Verify Email Address'))
                ->line(Lang::get('Please click the button below to verify your email address.'))
                ->action(Lang::get('Verify Email Address'), $verifyUrl)
                ->line(Lang::get('If you did not create an account, no further action is required.'));
		});
        Schema::defaultStringLength(191);
    }
}
