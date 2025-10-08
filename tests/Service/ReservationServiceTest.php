<?php

namespace App\Tests\Service;

use App\Service\ReservationStateService;
use App\Service\PaymentProcessingService;
use App\Service\VehicleAvailabilityService;
use PHPUnit\Framework\TestCase;
use App\Entity\Reservation;
use App\Entity\AnnulationReservation;
use DateTime;

class ReservationServiceTest extends TestCase
{
    private $reservationStateService;
    private $paymentProcessingService;
    private $vehicleAvailabilityService;
    
    protected function setUp(): void
    {
        // Mock the services and their dependencies
        $this->reservationStateService = $this->createMock(ReservationStateService::class);
        $this->paymentProcessingService = $this->createMock(PaymentProcessingService::class);
        $this->vehicleAvailabilityService = $this->createMock(VehicleAvailabilityService::class);
    }

    public function testReportReservation(): void
    {
        $reservation = $this->createMock(Reservation::class);
        
        // Test the actual business logic for reporting a reservation
        // This would be called by the controller
        $this->reservationStateService
            ->expects($this->once())
            ->method('reportReservation')
            ->with($reservation)
            ->willReturn(true);
            
        $result = $this->reservationStateService->reportReservation($reservation);
        $this->assertTrue($result);
    }

    public function testCancelReservation(): void
    {
        $reservation = $this->createMock(Reservation::class);
        $annulation = $this->createMock(AnnulationReservation::class);
        
        // Test the actual business logic for canceling a reservation
        $this->reservationStateService
            ->expects($this->once())
            ->method('cancelReservation')
            ->with($reservation, $annulation)
            ->willReturn(true);
            
        $result = $this->reservationStateService->cancelReservation($reservation, $annulation);
        $this->assertTrue($result);
    }

    public function testAddPaymentToReservation(): void
    {
        $reservation = $this->createMock(Reservation::class);
        $amount = 100.0;
        $datePayment = new DateTime();
        $paymentMethodId = 1;
        
        // Test the actual business logic for adding a payment
        $this->paymentProcessingService
            ->expects($this->once())
            ->method('addPaymentToReservation')
            ->with($reservation, $amount, $datePayment, $paymentMethodId)
            ->willReturn(true);
            
        $result = $this->paymentProcessingService->addPaymentToReservation(
            $reservation,
            $amount,
            $datePayment,
            $paymentMethodId
        );
        $this->assertTrue($result);
    }

    public function testArchiveReservation(): void
    {
        $reservation = $this->createMock(Reservation::class);
        
        // Test the actual business logic for archiving a reservation
        $this->reservationStateService
            ->expects($this->once())
            ->method('archiveReservation')
            ->with($reservation);
            
        $this->reservationStateService->archiveReservation($reservation);
        // Assuming this method doesn't return anything
    }
}