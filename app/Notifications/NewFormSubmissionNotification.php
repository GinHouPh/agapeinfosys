<?php

namespace App\Notifications;

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewFormSubmissionNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Form $form,
        public readonly FormSubmission $submission,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * The announcement of a new submission.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->form->notificationSubject())
            ->line('A new submission has come in on the "'.$this->form->title.'" form.')
            ->line('Reference: '.$this->submission->reference);

        foreach ($this->form->fields as $field) {
            $answer = $this->submission->answerFor($field);

            if ($answer === '') {
                continue;
            }

            $message->line($field->label.': '.$answer);
        }

        return $message->line('Review it in the admin submissions inbox.');
    }
}
