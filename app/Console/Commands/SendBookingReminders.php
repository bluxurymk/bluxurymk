<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Mail\BookingMail;
use App\Mail\BookingReminder;
use App\Mail\DepositReminder;
use App\Models\Booking;
use App\Services\SalonSettings;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

#[Signature('bookings:send-reminders')]
#[Description('Send deposit reminders and upcoming appointment reminders')]
class SendBookingReminders extends Command
{
    public function handle(SalonSettings $settings): int
    {
        $settings->applyToConfig();

        $depositReminders = $this->sendDepositReminders();
        $appointmentReminders = $this->sendAppointmentReminders();

        $this->info("Sent {$depositReminders} deposit reminder(s) and {$appointmentReminders} appointment reminder(s).");

        return self::SUCCESS;
    }

    /**
     * Remind customers halfway through their deposit window (24h for the default 48h).
     */
    private function sendDepositReminders(): int
    {
        $sent = 0;

        Booking::query()
            ->where('status', BookingStatus::PendingDeposit)
            ->whereNull('deposit_reminder_sent_at')
            ->whereNotNull('customer_email')
            ->where('deposit_due_at', '>', now())
            ->lazyById()
            ->filter(fn (Booking $booking): bool => now()->greaterThanOrEqualTo(
                $booking->created_at->toImmutable()->addSeconds((int) ($booking->created_at->diffInSeconds($booking->deposit_due_at) / 2)),
            ))
            ->each(function (Booking $booking) use (&$sent): void {
                if ($this->claimAndSend($booking, 'deposit_reminder_sent_at', new DepositReminder($booking))) {
                    $sent++;
                }
            });

        return $sent;
    }

    /**
     * Remind confirmed customers of appointments starting within the reminder window.
     */
    private function sendAppointmentReminders(): int
    {
        $sent = 0;

        Booking::query()
            ->where('status', BookingStatus::Confirmed)
            ->whereNull('reminder_sent_at')
            ->whereNotNull('customer_email')
            ->where('starts_at', '>', now())
            ->where('starts_at', '<=', now()->addHours(config('salon.booking.reminder_hours')))
            ->lazyById()
            ->each(function (Booking $booking) use (&$sent): void {
                if ($this->claimAndSend($booking, 'reminder_sent_at', new BookingReminder($booking))) {
                    $sent++;
                }
            });

        return $sent;
    }

    /**
     * Mark the reminder as sent before queueing it, so overlapping runs never send it twice.
     */
    private function claimAndSend(Booking $booking, string $sentAtColumn, BookingMail $mail): bool
    {
        $claimed = Booking::query()
            ->whereKey($booking->getKey())
            ->whereNull($sentAtColumn)
            ->update([$sentAtColumn => now()]);

        if ($claimed === 0) {
            return false;
        }

        Mail::to($booking->customer_email, $booking->customer_name)->send($mail);

        return true;
    }
}
