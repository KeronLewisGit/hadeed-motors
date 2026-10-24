<?php

namespace App\Notifications;

use App\Enums\LeadType;
use App\Models\Lead;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** To the dealership: a new lead to call. Finance details stay in the dashboard, never in email. */
class NewLead extends Notification
{
    public function __construct(public readonly Lead $lead) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $l = $this->lead;
        $mail = (new MailMessage)
            ->subject("{$l->type->label()} {$l->reference}: {$l->name}")
            ->line("{$l->name} · ".format_phone($l->phone).($l->email ? " · {$l->email}" : ''));

        if ($subject = $l->subject()) {
            $mail->line('Vehicle: '.$subject);
        }
        if ($l->appointment_date) {
            $mail->line('Requested: '.$l->appointment_date->format('l j F').($l->appointment_time ? ' at '.$l->appointment_time : ''));
        }
        if ($l->type === LeadType::Finance) {
            $mail->line('Finance details and documents are only in the dashboard.');
        } elseif ($l->message) {
            $mail->line($l->message);
        }

        $mail->line('Please call within '.Lead::RESPONSE_HOURS.' hours.')
            ->action('Open the lead', route('admin.leads.show', $l));

        if ($l->email) {
            $mail->replyTo($l->email, $l->name);
        }

        return $mail;
    }
}
