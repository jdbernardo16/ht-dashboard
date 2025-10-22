<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\Config;

class MailConfigServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Configure mail settings to prevent quoted-printable encoding issues
        Config::set('mail.mailers.smtp.encoding', '8bit');
        Config::set('mail.mailers.smtp.charset', 'utf-8');
        
        // Configure all mailers to use proper encoding
        $mailers = Config::get('mail.mailers', []);
        
        foreach ($mailers as $name => $config) {
            if (is_array($config)) {
                Config::set("mail.mailers.{$name}.encoding", '8bit');
                Config::set("mail.mailers.{$name}.charset", 'utf-8');
            }
        }
    }
}