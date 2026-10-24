<?php

namespace App\Notifications;

use App\Enums\LeadType;
use App\Models\Lead;
use App\Support\Hours;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** To the customer: we have it, here is your reference, here is what happens next. */
class LeadReceived extends Notification
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
        $phone = format_phone((string) hadeed('phone'));
        $mail = (new MailMessage)
            ->subject("Hadeed Motors: your {$this->what()} ({$l->reference})")
            ->greeting('Thank you, '.strtok($l->name, ' ').'.')
            ->line("We have your {$this->what()}. Your reference is **{$l->reference}**.");

        if ($subject = $l->subject()) {
            $mail->line('Vehicle: '.$subject);
        }
        if ($l->appointment_date) {
            $mail->line('Requested time: '.$l->appointment_date->format('l j F').($l->appointment_time ? ' at '.$l->appointment_time : '').'. This is a request until we confirm it with you.');
        }

        $mail->line(match ($l->type) {
            LeadType::Finance => 'Finance & Development Co. will review your details and call you, usually within one working day. They will never ask for your bank PIN or online banking password.',
            LeadType::Service => 'Our service team will call to confirm your drop-off time.',
            LeadType::Parts => 'Our parts counter will check stock and call you with availability and price.',
            LeadType::TradeIn => 'A sales consultant will look at your photos and call you about a valuation. The final figure depends on an inspection.',
            default => 'A member of our team will call you during opening hours.',
        });

        return $mail
            ->line("Questions? Call {$phone}, ".Hours::summary().'.')
            ->salutation('Hadeed Motors, Old Parham Road, St John\'s');
    }

    private function what(): string
    {
        return match ($this->lead->type) {
            LeadType::TestDrive => 'test drive request',
            LeadType::Service => 'service booking request',
            LeadType::Parts => 'parts request',
            LeadType::TradeIn => 'trade-in valuation request',
            LeadType::Finance => 'finance pre-qualification',
            LeadType::Enquiry => 'message',
        };
    }
}
