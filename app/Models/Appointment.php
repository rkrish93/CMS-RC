<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;
    protected $fillable = [
        'patient_id',
        'unit_id',
        'appointment_date',
        'appointment_time',
        'token_no',
        'notes',
        'status'
    ];

    public static function syncPastNoShows(): void
    {
        static::query()
            ->whereDate('appointment_date', '<', date('Y-m-d'))
            ->whereNotIn('status', [
                AppointmentStatus::COMPLETED->value,
                AppointmentStatus::CONSULTATION_COMPLETED->value,
                AppointmentStatus::DISPENSING->value,
                AppointmentStatus::CANCELLED->value,
                AppointmentStatus::NO_SHOW->value,
            ])
            ->update(['status' => AppointmentStatus::NO_SHOW->value]);
    }

    public static function normalizeStatus(?string $status): string
    {
        return AppointmentStatus::fromValue($status)?->value ?? AppointmentStatus::SCHEDULED->value;
    }

    public static function validStatuses(): array
    {
        return AppointmentStatus::values();
    }

    public function getStatusLabelAttribute(): string
    {
        return (AppointmentStatus::fromValue($this->status) ?? AppointmentStatus::SCHEDULED)->getLabel();
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return (AppointmentStatus::fromValue($this->status) ?? AppointmentStatus::SCHEDULED)->getBadgeColor();
    }

    public function canTransitionTo(?string $status): bool
    {
        $current = AppointmentStatus::fromValue($this->status) ?? AppointmentStatus::SCHEDULED;
        $target = AppointmentStatus::fromValue($status);

        return $target !== null && $current->canTransitionTo($target);
    }

    public function transitionTo(?string $status): bool
    {
        $target = AppointmentStatus::fromValue($status);

        if ($target === null || ! $this->canTransitionTo($target->value)) {
            return false;
        }

        $this->status = $target->value;

        return $this->save();
    }
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    // public function doctor()
    // {
    //     return $this->belongsTo(User::class,'doctor_id');
    // }
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function consultation()
    {
        return $this->hasOne(Consultation::class);
    }

    public function vitals()
    {
        return $this->hasMany(Vital::class);
    }
}
