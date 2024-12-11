<?php

namespace App\Observers;

use App\Mail\NewUserMailable;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        if ($user->temporalPassword) {
            Mail::to($user->email)->send(new NewUserMailable($user, $user->temporalPassword));
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Solo enviar el correo si `temporalPassword` está configurado
        if (!empty($user->temporalPassword)) {
            Mail::to($user->email)->send(new NewUserMailable($user, $user->temporalPassword, 'reset_password'));
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
