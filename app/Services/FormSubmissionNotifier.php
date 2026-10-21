<?php

namespace App\Services;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Notifications\NewFormSubmissionNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Announces a new form submission to whoever asked to hear about them.
 */
class FormSubmissionNotifier
{
    /**
     * Send the notification, if the form asked for one.
     */
    public function notify(Form $form, FormSubmission $submission): void
    {
        $email = $form->notificationEmail();

        if ($email === null) {
            return;
        }

        Notification::route('mail', $email)
            ->notify(new NewFormSubmissionNotification($form, $submission));
    }
}
