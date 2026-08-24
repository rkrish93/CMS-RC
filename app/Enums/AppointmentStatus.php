<?php

namespace App\Enums;

enum AppointmentStatus: string
{
    case SCHEDULED = 'scheduled';
    case CHECKED_IN = 'checked_in';
    case TRIAGE_IN_PROGRESS = 'triage_in_progress';
    case TRIAGE_COMPLETED = 'triage_completed';
    case CONSULTATION_IN_PROGRESS = 'consultation_in_progress';
    case CONSULTATION_COMPLETED = 'consultation_completed';
    case DISPENSING = 'dispensing';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case NO_SHOW = 'no_show';

    public static function fromValue(?string $value): ?self
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $normalized = strtolower(str_replace([' ', '-'], '_', trim((string) $value)));

        return match ($normalized) {
            'pending', 'scheduled', 'schedule' => self::SCHEDULED,
            'checked_in', 'check_in' => self::CHECKED_IN,
            'in_progress' => self::TRIAGE_IN_PROGRESS,
            'nurse_done', 'triage_completed' => self::TRIAGE_COMPLETED,
            'consultation_in_progress' => self::CONSULTATION_IN_PROGRESS,
            'consultation_completed' => self::CONSULTATION_COMPLETED,
            'dispensing' => self::DISPENSING,
            'completed' => self::COMPLETED,
            'cancelled' => self::CANCELLED,
            'no_show', 'noshow' => self::NO_SHOW,
            default => null,
        };
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::SCHEDULED => 'Scheduled',
            self::CHECKED_IN => 'Checked In',
            self::TRIAGE_IN_PROGRESS => 'Triage In Progress',
            self::TRIAGE_COMPLETED => 'Triage Completed',
            self::CONSULTATION_IN_PROGRESS => 'Consultation In Progress',
            self::CONSULTATION_COMPLETED => 'Consultation Completed',
            self::DISPENSING => 'Dispensing',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::NO_SHOW => 'No Show',
        };
    }

    public function getBadgeColor(): string
    {
        return match ($this) {
            self::SCHEDULED => 'warning',
            self::CHECKED_IN => 'info',
            self::TRIAGE_IN_PROGRESS => 'primary',
            self::TRIAGE_COMPLETED => 'dark',
            self::CONSULTATION_IN_PROGRESS => 'primary',
            self::CONSULTATION_COMPLETED => 'success',
            self::DISPENSING => 'secondary',
            self::COMPLETED => 'success',
            self::CANCELLED => 'danger',
            self::NO_SHOW => 'secondary',
        };
    }

    public function canTransitionTo(self $target): bool
    {
        $order = [
            self::SCHEDULED,
            self::CHECKED_IN,
            self::TRIAGE_IN_PROGRESS,
            self::TRIAGE_COMPLETED,
            self::CONSULTATION_IN_PROGRESS,
            self::CONSULTATION_COMPLETED,
            self::DISPENSING,
            self::COMPLETED,
        ];

        if ($target === self::CANCELLED || $target === self::NO_SHOW) {
            return ! in_array($this, [self::COMPLETED, self::CANCELLED, self::NO_SHOW], true);
        }

        if ($this === self::COMPLETED || $this === self::CANCELLED || $this === self::NO_SHOW) {
            return false;
        }

        $fromIndex = array_search($this, $order, true);
        $toIndex = array_search($target, $order, true);

        if ($fromIndex === false || $toIndex === false) {
            return false;
        }

        return $toIndex === $fromIndex + 1;
    }

    public static function values(): array
    {
        return array_map(static fn (self $status): string => $status->value, self::cases());
    }
}
