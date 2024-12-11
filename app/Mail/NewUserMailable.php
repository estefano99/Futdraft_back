<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewUserMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $password;
    public $context; // Nuevo campo para diferenciar creación de usuario o reseteo

    /**
     * Create a new message instance.
     *
     * @param $user
     * @param $password
     * @param string $context
     */
    public function __construct($user, $password, $context = 'new_user')
    {
        $this->user = $user;
        $this->password = $password;
        $this->context = $context;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address("estefanobugari@gmail.com", "Club Sportivo Bombal"),
            subject: $this->context === 'reset_password'
                ? 'Su contraseña ha sido reseteada'
                : 'Su cuenta ha sido creada',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $viewName = $this->context === 'reset_password'
            ? 'emails.reset-password'
            : 'emails.new-user';

        return new Content(
            view: $viewName,
            with: [
                'user' => $this->user,
                'password' => $this->password,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
