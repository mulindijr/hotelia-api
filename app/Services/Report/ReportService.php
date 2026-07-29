<?php

namespace App\Services\Report;

use App\Models\Hotel;
use App\Models\HousekeepingTask;
use App\Models\Invoice;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Compute operational dashboard stats with 5-minute caching per hotel.
     */
    public function getDashboardStats(Hotel $hotel): array
    {
        return Cache::remember("hotel:{$hotel->id}:dashboard_stats", 300, function () use ($hotel) {
            $roomIds = $hotel->rooms()->pluck('id')->toArray();
            $totalRooms = count($roomIds);

            // 1. Occupancy statistics
            $occupiedCount = $hotel->rooms()->where('status', 'occupied')->count();
            $occupancyRate = $totalRooms > 0 ? round(($occupiedCount / $totalRooms) * 100, 2) : 0.00;

            // 2. Room status breakdown
            $roomStatuses = $hotel->rooms()
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            // 3. Housekeeping stats
            $housekeepingStats = HousekeepingTask::whereIn('room_id', $roomIds)
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            // 4. Maintenance stats (open or in_progress)
            $activeMaintenance = MaintenanceRequest::whereIn('room_id', $roomIds)
                ->whereIn('status', ['open', 'in_progress'])
                ->count();

            // 5. Active checked-in bookings
            $activeBookingsCount = $hotel->bookings()->where('status', 'checked_in')->count();

            return [
                'total_rooms' => $totalRooms,
                'occupied_rooms' => $occupiedCount,
                'occupancy_rate' => $occupancyRate,
                'room_statuses' => [
                    'available' => $roomStatuses['available'] ?? 0,
                    'occupied' => $roomStatuses['occupied'] ?? 0,
                    'cleaning' => $roomStatuses['cleaning'] ?? 0,
                    'maintenance' => $roomStatuses['maintenance'] ?? 0,
                ],
                'housekeeping_tasks' => [
                    'pending' => $housekeepingStats['pending'] ?? 0,
                    'in_progress' => $housekeepingStats['in_progress'] ?? 0,
                    'completed' => $housekeepingStats['completed'] ?? 0,
                ],
                'active_maintenance_requests' => $activeMaintenance,
                'active_bookings_count' => $activeBookingsCount,
            ];
        });
    }

    /**
     * Compute date range based financial performance KPIs.
     */
    public function getRevenueStats(Hotel $hotel, ?string $startDate = null, ?string $endDate = null): array
    {
        $startDate = $startDate ? Carbon::parse($startDate)->startOfDay()->toDateTimeString() : now()->subDays(30)->startOfDay()->toDateTimeString();
        $endDate = $endDate ? Carbon::parse($endDate)->endOfDay()->toDateTimeString() : now()->endOfDay()->toDateTimeString();

        $bookingIds = $hotel->bookings()->pluck('id')->toArray();

        // 1. Total payments collected
        $paymentsCollected = Payment::whereIn('booking_id', $bookingIds)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount');

        // 2. Invoiced totals
        $totalInvoiced = Invoice::whereIn('booking_id', $bookingIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total_amount');

        // 3. ADR & RevPAR stay calculations
        $totalNightsSold = 0;
        $totalRoomRevenue = 0.00;

        $bookings = $hotel->bookings()
            ->where('check_in_date', '<=', $endDate)
            ->where('check_out_date', '>=', $startDate)
            ->where('status', '!=', 'cancelled')
            ->with('rooms')
            ->get();

        foreach ($bookings as $booking) {
            $checkIn = Carbon::parse($booking->check_in_date);
            $checkOut = Carbon::parse($booking->check_out_date);
            $nights = max(1, $checkIn->diffInDays($checkOut));

            foreach ($booking->rooms as $room) {
                $totalNightsSold += $nights;
                $totalRoomRevenue += ($room->pivot->price_per_night ?? 0.00) * $nights;
            }
        }

        $adr = $totalNightsSold > 0 ? round($totalRoomRevenue / $totalNightsSold, 2) : 0.00;

        // RevPAR: Room Revenue / Total Available Room Nights in period
        $totalRooms = $hotel->rooms()->count();
        $daysInPeriod = max(1, Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)));
        $totalAvailableNights = $totalRooms * $daysInPeriod;

        $revpar = $totalAvailableNights > 0 ? round($totalRoomRevenue / $totalAvailableNights, 2) : 0.00;

        // 4. Payment methods breakdown
        $methodCounts = Payment::whereIn('booking_id', $bookingIds)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('payment_method', DB::raw('sum(amount) as total'))
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method')
            ->toArray();

        return [
            'period' => [
                'start_date' => Carbon::parse($startDate)->toDateString(),
                'end_date' => Carbon::parse($endDate)->toDateString(),
                'days' => $daysInPeriod,
            ],
            'payments_collected' => round($paymentsCollected, 2),
            'total_invoiced' => round($totalInvoiced, 2),
            'room_revenue' => round($totalRoomRevenue, 2),
            'nights_sold' => $totalNightsSold,
            'adr' => $adr,
            'revpar' => $revpar,
            'payment_methods' => [
                'cash' => round($methodCounts['cash'] ?? 0.00, 2),
                'card' => round($methodCounts['card'] ?? 0.00, 2),
                'mpesa' => round($methodCounts['mpesa'] ?? 0.00, 2),
                'bank_transfer' => round($methodCounts['bank_transfer'] ?? 0.00, 2),
            ],
        ];
    }
}
