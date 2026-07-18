<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Booking;
use App\Models\BookingRoom;
use App\Models\BookingService;
use App\Models\Guest;
use App\Models\Hotel;
use App\Models\HotelSetting;
use App\Models\HousekeepingTask;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\MaintenanceRequest;
use App\Models\Payment;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Amenities (we will reuse these across room types)
        $wifi = Amenity::factory()->wifi()->create();
        $ac = Amenity::factory()->ac()->create();
        $pool = Amenity::factory()->pool()->create();
        $gym = Amenity::factory()->gym()->create();
        $minibar = Amenity::factory()->minibar()->create();
        
        $allAmenities = [$wifi, $ac, $pool, $gym, $minibar];

        // 2. Define Hotels configurations to seed
        $hotelsData = [
            [
                'name' => 'The Grand Palace Resort',
                'type' => 'luxury',
                'prefix' => 'GP',
                'currency' => 'USD',
                'timezone' => 'Africa/Nairobi',
                'tax_rate' => 16.00,
            ],
            [
                'name' => 'Apex Business Hotel',
                'type' => 'standard',
                'prefix' => 'APX',
                'currency' => 'KES',
                'timezone' => 'Africa/Nairobi',
                'tax_rate' => 16.00,
            ],
            [
                'name' => 'Travelers Budget Inn',
                'type' => 'budget',
                'prefix' => 'TBI',
                'currency' => 'KES',
                'timezone' => 'Africa/Nairobi',
                'tax_rate' => 16.00,
            ],
        ];

        foreach ($hotelsData as $index => $hData) {
            // Create Hotel
            $hotel = Hotel::factory();
            if ($hData['type'] === 'luxury') {
                $hotel = $hotel->luxury();
            } elseif ($hData['type'] === 'budget') {
                $hotel = $hotel->budget();
            }
            $hotel = $hotel->create([
                'name' => $hData['name']
            ]);

            // Update or create settings to match config
            $hotel->settings()->updateOrCreate(
                ['hotel_id' => $hotel->id],
                [
                    'currency' => $hData['currency'],
                    'timezone' => $hData['timezone'],
                    'booking_prefix' => $hData['prefix'],
                    'invoice_prefix' => $hData['prefix'] . '-INV',
                    'tax_rate' => $hData['tax_rate'],
                    'allow_overbooking' => $hData['type'] === 'luxury',
                ]
            );

            // Create Staff Members for this Hotel
            $staffRoles = [
                'hotel_manager' => 'manager',
                'receptionist' => 'reception',
                'housekeeper' => 'cleaner',
                'accountant' => 'finance',
            ];

            $staffUsers = [];
            foreach ($staffRoles as $roleName => $emailPrefix) {
                $staff = User::factory()->create([
                    'first_name' => ucfirst($roleName),
                    'last_name' => $hotel->name,
                    'email' => "{$emailPrefix}.{$index}@hotelia.com",
                    'password' => Hash::make('Password@123'),
                ]);
                $staff->assignRole($roleName);
                $hotel->users()->attach($staff->id);
                $staffUsers[$roleName] = $staff;
            }

            // Create Hotel Services
            $spaService = Service::factory()->spa()->create(['hotel_id' => $hotel->id]);
            $laundryService = Service::factory()->laundry()->create(['hotel_id' => $hotel->id]);
            $shuttleService = Service::factory()->airportShuttle()->create(['hotel_id' => $hotel->id]);
            $services = [$spaService, $laundryService, $shuttleService];

            // Create Room Types
            $standardType = RoomType::factory()->create([
                'hotel_id' => $hotel->id,
                'name' => 'Standard Room',
                'base_price' => $hData['type'] === 'luxury' ? 8000.00 : 3500.00,
            ]);
            $standardType->amenities()->attach([$wifi->id, $ac->id]);

            $deluxeType = RoomType::factory()->deluxe()->create([
                'hotel_id' => $hotel->id,
                'base_price' => $hData['type'] === 'luxury' ? 14000.00 : 6500.00,
            ]);
            $deluxeType->amenities()->attach([$wifi->id, $ac->id, $minibar->id]);

            $suiteType = RoomType::factory()->suite()->create([
                'hotel_id' => $hotel->id,
                'base_price' => $hData['type'] === 'luxury' ? 25000.00 : 12000.00,
            ]);
            $suiteType->amenities()->attach([$wifi->id, $ac->id, $minibar->id, $pool->id, $gym->id]);

            // Create Rooms
            $rooms = [];
            $roomTypes = [$standardType, $deluxeType, $suiteType];

            for ($floor = 1; $floor <= 3; $floor++) {
                foreach ($roomTypes as $rTypeIndex => $rType) {
                    for ($roomNum = 1; $roomNum <= 2; $roomNum++) {
                        $number = "{$floor}0" . (($rTypeIndex * 2) + $roomNum);
                        $rooms[] = Room::factory()->create([
                            'hotel_id' => $hotel->id,
                            'room_type_id' => $rType->id,
                            'room_number' => $hData['prefix'] . '-' . $number,
                            'floor' => $floor,
                            'status' => 'available',
                        ]);
                    }
                }
            }

            // Create Guests
            $guests = Guest::factory()->count(10)->create();

            // Create Bookings with varying states
            foreach ($guests as $gIndex => $guest) {
                // Assign a random room for this guest
                $room = $rooms[$gIndex % count($rooms)];

                // Vary booking status & dates
                if ($gIndex < 4) {
                    // Past Bookings (Checked Out)
                    $booking = Booking::factory()->checkedOut()->create([
                        'hotel_id' => $hotel->id,
                        'guest_id' => $guest->id,
                        'status' => 'checked_out',
                    ]);

                    BookingRoom::create([
                        'booking_id' => $booking->id,
                        'room_id' => $room->id,
                        'price_per_night' => $room->roomType->base_price,
                    ]);

                    // Invoice
                    $invoice = Invoice::factory()->paid()->create([
                        'booking_id' => $booking->id,
                        'subtotal' => $booking->total_amount,
                        'tax_amount' => round($booking->total_amount * 0.16, 2),
                        'total_amount' => round($booking->total_amount * 1.16, 2),
                    ]);

                    InvoiceItem::factory()->create([
                        'invoice_id' => $invoice->id,
                        'description' => "Room Accommodation Charge - {$room->roomType->name}",
                        'quantity' => 2,
                        'unit_price' => $room->roomType->base_price,
                        'total_price' => $invoice->subtotal,
                    ]);

                    // Payment
                    Payment::factory()->completed()->create([
                        'booking_id' => $booking->id,
                        'amount' => $invoice->total_amount,
                    ]);

                    // Mark room as available since they checked out
                    $room->update(['status' => 'available']);
                } elseif ($gIndex < 7) {
                    // Current Bookings (Checked In)
                    $booking = Booking::factory()->checkedIn()->create([
                        'hotel_id' => $hotel->id,
                        'guest_id' => $guest->id,
                        'status' => 'checked_in',
                    ]);

                    BookingRoom::create([
                        'booking_id' => $booking->id,
                        'room_id' => $room->id,
                        'price_per_night' => $room->roomType->base_price,
                    ]);

                    // Add a service
                    $chosenService = $services[$gIndex % count($services)];
                    BookingService::factory()->create([
                        'booking_id' => $booking->id,
                        'service_id' => $chosenService->id,
                        'price' => $chosenService->price,
                    ]);

                    // Invoice (Unpaid/Partial)
                    $invoice = Invoice::factory()->unpaid()->create([
                        'booking_id' => $booking->id,
                        'subtotal' => $booking->total_amount,
                        'tax_amount' => round($booking->total_amount * 0.16, 2),
                        'total_amount' => round($booking->total_amount * 1.16, 2),
                    ]);

                    // Mark room as occupied
                    $room->update(['status' => 'occupied']);

                    // Create a pending housekeeping task
                    HousekeepingTask::factory()->pending()->create([
                        'room_id' => $room->id,
                        'assigned_to' => $staffUsers['housekeeper']->id,
                    ]);
                } elseif ($gIndex < 9) {
                    // Future Bookings (Confirmed)
                    $booking = Booking::factory()->confirmed()->create([
                        'hotel_id' => $hotel->id,
                        'guest_id' => $guest->id,
                        'status' => 'confirmed',
                    ]);

                    BookingRoom::create([
                        'booking_id' => $booking->id,
                        'room_id' => $room->id,
                        'price_per_night' => $room->roomType->base_price,
                    ]);

                    // Mark room as reserved
                    $room->update(['status' => 'reserved']);
                } else {
                    // Cancelled Bookings
                    $booking = Booking::factory()->cancelled()->create([
                        'hotel_id' => $hotel->id,
                        'guest_id' => $guest->id,
                        'status' => 'cancelled',
                    ]);

                    BookingRoom::create([
                        'booking_id' => $booking->id,
                        'room_id' => $room->id,
                        'price_per_night' => $room->roomType->base_price,
                    ]);
                }
            }

            // Create Maintenance Requests for 2 rooms
            MaintenanceRequest::factory()->high()->inProgress()->create([
                'room_id' => $rooms[0]->id,
                'reported_by' => $staffUsers['hotel_manager']->id,
            ]);
            $rooms[0]->update(['status' => 'maintenance']);

            MaintenanceRequest::factory()->low()->resolved()->create([
                'room_id' => $rooms[1]->id,
                'reported_by' => $staffUsers['receptionist']->id,
            ]);
        }
    }
}
