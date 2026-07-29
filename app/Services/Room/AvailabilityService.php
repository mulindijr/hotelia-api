<?php

namespace App\Services\Room;

use App\Constants\BookingStatus;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\RatePlan;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\Pricing\PricingService;
use Carbon\Carbon;

class AvailabilityService
{
    public function __construct(
        protected PricingService $pricingService
    ) {}

    /**
     * Get availability matrix per room type for a specified date range.
     */
    public function getAvailabilityMatrix(
        Hotel $hotel,
        Carbon $startDate,
        Carbon $endDate,
        ?int $roomTypeId = null,
        ?int $ratePlanId = null
    ): array {
        $roomTypesQuery = RoomType::where('hotel_id', $hotel->id);
        if ($roomTypeId) {
            $roomTypesQuery->where('id', $roomTypeId);
        }
        $roomTypes = $roomTypesQuery->get();

        $ratePlan = null;
        if ($ratePlanId) {
            $ratePlan = RatePlan::where('hotel_id', $hotel->id)->find($ratePlanId);
        }

        // Preload active rooms count by room_type_id
        $totalRoomsMap = Room::where('hotel_id', $hotel->id)
            ->selectRaw('room_type_id, COUNT(*) as total')
            ->groupBy('room_type_id')
            ->pluck('total', 'room_type_id')
            ->toArray();

        // Fetch active bookings in date window
        $startDateStr = $startDate->toDateString();
        $endDateStr = $endDate->toDateString();

        $activeBookings = Booking::where('hotel_id', $hotel->id)
            ->whereNotIn('status', [BookingStatus::CANCELLED, BookingStatus::NO_SHOW])
            ->where('check_in_date', '<', $endDateStr)
            ->where('check_out_date', '>', $startDateStr)
            ->with(['rooms:id,room_type_id'])
            ->get();

        $stayNights = max(1, $startDate->diffInDays($endDate));

        $matrix = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lt($endDate)) {
            $dateStr = $currentDate->toDateString();
            $dayData = [
                'date' => $dateStr,
                'day_of_week' => $currentDate->format('l'),
                'room_types' => [],
            ];

            foreach ($roomTypes as $roomType) {
                $totalRooms = (int) ($totalRoomsMap[$roomType->id] ?? 0);

                // Count booked rooms of this room_type on current date
                $bookedCount = 0;
                foreach ($activeBookings as $booking) {
                    if ($booking->check_in_date->lte($currentDate) && $booking->check_out_date->gt($currentDate)) {
                        foreach ($booking->rooms as $room) {
                            if ((int) $room->room_type_id === (int) $roomType->id) {
                                $bookedCount++;
                            }
                        }
                    }
                }

                $availableRooms = max(0, $totalRooms - $bookedCount);
                $nightlyPrice = $this->pricingService->calculateNightlyPrice(
                    $roomType,
                    $ratePlan,
                    $currentDate,
                    $stayNights
                );

                $dayData['room_types'][] = [
                    'room_type_id' => $roomType->id,
                    'room_type_name' => $roomType->name,
                    'total_rooms' => $totalRooms,
                    'booked_rooms' => $bookedCount,
                    'available_rooms' => $availableRooms,
                    'price' => $nightlyPrice,
                    'status' => $availableRooms > 0 ? 'available' : 'sold_out',
                ];
            }

            $matrix[] = $dayData;
            $currentDate->addDay();
        }

        return [
            'hotel_id' => $hotel->id,
            'start_date' => $startDateStr,
            'end_date' => $endDateStr,
            'total_days' => count($matrix),
            'matrix' => $matrix,
        ];
    }
}
