<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StudentAcceptedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $student;
    public $token;

    /**
     * Create a new message instance.
     *
     * @param $student
     * @param $token
     */
    public function __construct($student, $token)
    {
        $this->student = $student;
        $this->token = $token;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $passwordSetupLink = url('/set-password?token=' . $this->token . '&email=' . urlencode($this->student->email));

        return $this->subject('You have been accepted! Set up your password')
            ->view('emails.studentsaccepted')
            ->with([
                'student' => $this->student,
                'passwordSetupLink' => $passwordSetupLink,
            ]);
    }
}
