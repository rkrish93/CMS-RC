<?php

namespace Tests\Unit;

use App\Enums\AppointmentStatus;
use PHPUnit\Framework\TestCase;

class AppointmentStatusTest extends TestCase
{
    public function test_legacy_statuses_map_to_the_new_workflow(): void
    {
        $this->assertSame(AppointmentStatus::SCHEDULED->value, AppointmentStatus::fromValue('pending')?->value);
        $this->assertSame(AppointmentStatus::TRIAGE_IN_PROGRESS->value, AppointmentStatus::fromValue('in_progress')?->value);
        $this->assertSame(AppointmentStatus::TRIAGE_COMPLETED->value, AppointmentStatus::fromValue('nurse_done')?->value);
    }

    public function test_status_transitions_follow_the_new_workflow(): void
    {
        $this->assertTrue(AppointmentStatus::SCHEDULED->canTransitionTo(AppointmentStatus::CHECKED_IN));
        $this->assertTrue(AppointmentStatus::CHECKED_IN->canTransitionTo(AppointmentStatus::TRIAGE_IN_PROGRESS));
        $this->assertTrue(AppointmentStatus::TRIAGE_IN_PROGRESS->canTransitionTo(AppointmentStatus::TRIAGE_COMPLETED));
        $this->assertTrue(AppointmentStatus::TRIAGE_COMPLETED->canTransitionTo(AppointmentStatus::CONSULTATION_IN_PROGRESS));
        $this->assertTrue(AppointmentStatus::CONSULTATION_IN_PROGRESS->canTransitionTo(AppointmentStatus::CONSULTATION_COMPLETED));
        $this->assertTrue(AppointmentStatus::CONSULTATION_COMPLETED->canTransitionTo(AppointmentStatus::DISPENSING));
        $this->assertTrue(AppointmentStatus::DISPENSING->canTransitionTo(AppointmentStatus::COMPLETED));
    }

    public function test_terminal_statuses_can_be_used_for_cancellation_and_no_show(): void
    {
        $this->assertTrue(AppointmentStatus::SCHEDULED->canTransitionTo(AppointmentStatus::CANCELLED));
        $this->assertTrue(AppointmentStatus::CHECKED_IN->canTransitionTo(AppointmentStatus::NO_SHOW));
        $this->assertFalse(AppointmentStatus::COMPLETED->canTransitionTo(AppointmentStatus::CANCELLED));
    }
}
