# 🏨 Hotelia API

### Commercial-Grade Multi-Hotel Property Management System

[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)
[![PHPUnit](https://img.shields.io/badge/Tests-PHPUnit_12-blue?style=for-the-badge&logo=php&logoColor=white)](https://phpunit.de)
[![API](https://img.shields.io/badge/API-RESTful_v1-orange?style=for-the-badge)](https://swagger.io)

**Hotelia API** is a multi-tenant RESTful API backend for a Hotel Property Management System (PMS). Built with Laravel 13, it supports hotel operations — from reservations and front-desk management to billing, housekeeping, maintenance, dynamic pricing, and real-time audit trails.

---

## 📋 Table of Contents

- [Overview](#-overview)
- [Project Status](#-project-status)
- [Features](#-features)
- [Technology Stack](#-technology-stack)
- [Installed Packages](#-installed-packages)
- [Architecture](#-architecture)
- [Folder Structure](#-folder-structure)
- [Database](#-database)
- [API](#-api)
- [Security](#-security)
- [Events & Listeners](#-events--listeners)
- [Notifications](#-notifications)
- [Queues & Scheduled Jobs](#-queues--scheduled-jobs)
- [Installation](#-installation)
- [Environment Variables](#-environment-variables)
- [Running Tests](#-running-tests)
- [API Documentation](#-api-documentation)
- [Development Workflow](#-development-workflow)
- [Roadmap](#-roadmap)
- [Contributing](#-contributing)
- [License](#-license)
- [Author](#-author)

---

## 🌐 Overview

**Hotelia API** solves a core problem in the hospitality industry: fragmented, expensive, or poorly integrated property management tools. It provides a single, cohesive REST API that powers all hotel operations from a central backend.

### What It Does

- Manages multiple hotels and their staff under one system (multi-tenancy via hotel-user pivot)
- Handles the full guest journey: reservation → check-in → check-out → invoicing → payment
- Automates housekeeping workflows triggered by guest check-out
- Calculates dynamic nightly rates via rate plans and pricing rules
- Exposes a room availability matrix with per-night pricing for date ranges
- Tracks maintenance requests tied to specific rooms
- Generates financial reports including ADR and RevPAR KPIs
- Enforces fine-grained, role-based access control across every endpoint
- Records a full, tamper-evident audit log of every state change

### Target Users

| User | Role |
|---|---|
| Hotel Chains | Multi-property centralized management |
| Boutique Hotels | Front desk, billing, and housekeeping automation |
| System Integrators | Headless API to power custom front-ends or mobile apps |
| Developers | Clean codebase to extend or white-label |

### Commercial Objectives

- Reduce manual front-desk errors via structured workflows and server-side validation
- Improve occupancy reporting with real-time dashboard stats and revenue KPIs
- Ensure data security and compliance through audit logging and access control
- Provide a scalable, headless API consumed by any client (web, mobile, kiosk)

---

## 📊 Project Status

Hotelia API is a **headless backend** — there is no admin UI, guest portal, or mobile app in this repository. The API layer is substantially complete; the remaining work is mostly integrations, a frontend, and production operations.

| Scope | Completion | Notes |
|---|---|---|
| **Core backend API** | ~78% | Auth, hotels, rooms, bookings, billing, housekeeping, maintenance, reports, pricing, availability |
| **Full commercial product** | ~45% | Requires UI, payment gateways, OTA/channel sync, onboarding, and deployment tooling |
| **Test suite** | 231 tests | Feature, unit, job, and performance coverage (run `composer run test`) |

See the [Roadmap](#-roadmap) for what is implemented vs. still planned.

---

## ✨ Features

### 🔐 Authentication

- Token-based authentication via **Laravel Sanctum**
- Login with account lockout (locked after 3 failed attempts, 15-minute cooldown)
- Logout (current device) and Logout All (all devices)
- Token refresh without requiring re-login
- Password change with **history enforcement** (last 5 passwords blocked)
- Forgot password / Reset password via signed tokenized email link
- Login history tracking per user (IP address, user agent, timestamps)
- Active session status endpoint (`GET /auth/status`)

### 🛡️ Authorization

- Role-Based Access Control (RBAC) via **Spatie Laravel Permission**
- Five defined roles: `super_admin`, `hotel_manager`, `receptionist`, `housekeeper`, `accountant`
- 45 granular permissions (e.g., `view bookings`, `check in guests`, `manage rate plans`)
- Permission-based middleware on every protected route
- Laravel **Policies** for object-level authorization (e.g., ensuring a booking belongs to the correct hotel)
- Cross-tenant route binding protection via scoped bindings

### 🏨 Hotel Management

- Full CRUD for hotels (name, slug, email, phone, country, city, address, description, logo)
- Soft deletes with cascade resource cleanup
- Hotel-to-user membership management (many-to-many)
- User unlock functionality (admin unlocks locked accounts)
- Activity logging on all hotel changes

### ⚙️ Hotel Settings

- Per-hotel configurable settings: currency, timezone, check-in/check-out time, tax rate
- Configurable booking and invoice number prefixes
- Late checkout fee and early check-in fee configuration
- Booking cancellation window (hours) and overbooking flag
- Language preference per hotel
- Grace period for checkout (minutes)

### 🛏️ Room Management

- Room Types with base pricing, capacity, and amenity associations (many-to-many)
- Rooms linked to room types per hotel with unique room number constraint per hotel
- Room status lifecycle: `available` → `occupied` → `cleaning` → `available` (or `maintenance`)
- Room status history tracking
- Global Amenities catalog (CRUD) shared across the system
- Room type ↔ amenity pivot relationships

### 👤 Guest Management

- Full guest profile management (name, email, phone, nationality, ID document details)
- Guests scoped to hotel context via booking relationships
- Soft deletes with activity logging

### 📅 Booking Engine

- Create bookings with **pessimistic locking** to prevent double-bookings under concurrent requests
- Date-range overlap detection before any room assignment
- Automatic booking reference number generation with hotel-specific prefix
- Optional **rate plan** attachment per booking (BAR, corporate, group, etc.)
- Automatic total amount computation via `PricingService` (nightly rate × nights + ancillary services)
- Booking status lifecycle: `pending` → `confirmed` → `checked_in` → `checked_out` | `cancelled` | `no_show`
- Booking status history automatically recorded on every transition
- Attach ancillary services to bookings with quantity and price
- Check-in: updates booked rooms to `occupied`
- Check-out: updates rooms to `cleaning` and **auto-creates housekeeping tasks**
- Booking cancellation with event dispatch

### 💳 Payments

- Log payments against bookings (cash, card, M-Pesa, bank transfer)
- Payment status management (`pending`, `completed`, `failed`, `refunded`)
- Invoice auto-reconciliation after each payment

### 🧾 Invoicing

- Auto-generate invoice on demand or retrieve existing one
- Invoice items: room stay line items + service line items
- Tax-inclusive total calculation using hotel's configured tax rate
- Invoice status auto-calculated: `unpaid` → `partial` → `paid`
- Invoice number generation with hotel-specific prefix
- Invoice regeneration endpoint (recomputes all line items)
- **PDF invoice download** via DomPDF (`GET .../invoice/pdf`)

### 💰 Pricing & Rate Plans

- Named rate plans per hotel (code, modifier type, cancellation policy, meal plan)
- Percentage or fixed price modifiers applied on top of room type base price
- **Pricing rules** with date ranges, day-of-week filters, min/max stay, and priority ordering
- Modifier types: `override`, `percentage`, or `fixed` adjustments
- Rules can be scoped globally, to a rate plan, or to a specific room type
- Rate plan selection integrated into booking creation and availability pricing

### 📆 Room Availability

- Availability matrix endpoint: `GET /api/v1/hotels/{hotel}/availability`
- Per room type, per day: total rooms, booked count, available count, and calculated nightly price
- Filterable by date range, room type, and rate plan
- Uses active booking overlap logic (excludes cancelled and no-show bookings)

### 📧 Transactional Email

- Guest booking emails: confirmation, update, cancellation, and no-show
- Billing emails: invoice generated and invoice paid
- Password reset via signed tokenized email link (Laravel `ResetPassword` + `FRONTEND_URL`)
- Blade email templates under `resources/views/emails/`
- Triggered by domain events through dedicated mail listeners (configure SMTP in `.env`)

### 📄 PDF Documents

- Invoice PDF generation with line items, tax breakdown, and hotel branding fields
- Payment receipt PDF download per completed payment
- Rendered via **barryvdh/laravel-dompdf** with Blade templates under `resources/views/pdf/`

### 🧹 Housekeeping

- Create, assign, update, and delete housekeeping tasks per room
- Task statuses: `pending` → `in_progress` → `completed`
- Auto-creates tasks on guest checkout (triggered by `BookingService::checkOut`)
- On task completion: room automatically set to `available` (unless active maintenance exists)
- Notifications dispatched on task creation and updates

### 🔧 Maintenance

- Maintenance requests per room with priority and status tracking
- Statuses: `open` → `in_progress` → `resolved` | `closed`
- Room status set to `maintenance` when active requests exist
- Notifications dispatched on request creation
- Integrated with housekeeping: completed cleaning defers to `maintenance` status if requests are open

### 🔔 In-App Notifications

- Database-channel notifications for bookings, housekeeping, and maintenance events
- Notification listing and mark-as-read endpoints
- `BaseNotification` abstract class for consistent notification structure
- Notification types managed via `NotificationTypes` constants class

### 📊 Audit Logging

- Automatic activity logging on all major models via `LogsAuditTrail` trait (wraps Spatie Activity Log)
- Logs only dirty (changed) attributes — no noise from unchanged fields
- Ignored fields per model (e.g., `last_login_at`, `failed_login_count` on User)
- Admin audit log endpoint with filtering support
- Failed login attempts logged separately per IP/user agent

### 🔒 Security

- Account lockout after 3 failed login attempts (15-minute cooldown)
- Password history enforcement (last 5 passwords blocked)
- Rate limiting: 5 req/min on login, 10 req/min on sensitive operations, 60 req/min global API
- Tiered rate limiters configured in `AppServiceProvider`
- Cross-tenant isolation via Laravel route `scopeBindings()`
- Policy-enforced object-level authorization on every mutation
- Failed login attempt tracking (IP, user agent, timestamp)
- Login history with logout timestamps (per-session)
- Soft deletes across all major entities
- Token revocation on password change (all other sessions)

### 📈 Reports

- **Dashboard Stats**: total rooms, occupied rooms, occupancy rate, room status breakdown, housekeeping stats, active maintenance count, active bookings count with 5-minute caching
- **Revenue Stats**: payments collected, total invoiced, room revenue, nights sold, **ADR** (Average Daily Rate), **RevPAR** (Revenue Per Available Room), payment method breakdown filterable by date range

### 🌐 API

- RESTful JSON API versioned under `/api/v1`
- Swagger / OpenAPI 3.0 documentation (L5-Swagger) with PHP 8 attributes
- Spatie Query Builder integration for filtering, sorting, and pagination
- Consistent JSON response structure (`success`, `message`, `data`)
- Scoped route model binding for multi-tenant resource isolation

---

## 🛠️ Technology Stack

| Layer | Technology |
|---|---|
| **Language** | PHP 8.3+ |
| **Framework** | Laravel 13.x |
| **Authentication** | Laravel Sanctum 4.x |
| **Authorization** | Spatie Laravel Permission 8.x |
| **Database** | SQLite (development/testing), MySQL/PostgreSQL (production-ready) |
| **Queue Driver** | Database (configurable - Redis recommended for production) |
| **API Documentation** | L5-Swagger (OpenAPI 3.0) |
| **Audit Logging** | Spatie Laravel Activity Log 5.x |
| **API Querying** | Spatie Laravel Query Builder 7.x |
| **PDF Generation** | barryvdh/laravel-dompdf 3.x |
| **Testing** | PHPUnit 12.x |
| **Code Style** | Laravel Pint |
| **IDE Support** | Barryvdh Laravel IDE Helper |
| **Debugging** | Barryvdh Laravel Debugbar |
| **Log Tailing** | Laravel Pail |
| **Concurrency (Dev)** | npx concurrently |

---

## 📦 Installed Packages

### Production Dependencies

| Package | Version | Purpose |
|---|---|---|
| `laravel/framework` | ^13.8 | Core framework |
| `laravel/sanctum` | ^4.3 | API token authentication |
| `laravel/tinker` | ^3.0 | REPL for development |
| `spatie/laravel-permission` | ^8.0 | Roles & permissions (RBAC) |
| `spatie/laravel-activitylog` | ^5.0 | Audit logging |
| `spatie/laravel-query-builder` | ^7.3 | Filtering, sorting & pagination |
| `barryvdh/laravel-dompdf` | ^3.1 | Invoice and payment receipt PDF generation |
| `darkaonline/l5-swagger` | ^11.0 | OpenAPI / Swagger documentation |

### Development Dependencies

| Package | Version | Purpose |
|---|---|---|
| `phpunit/phpunit` | ^12.5 | Unit and feature testing |
| `fakerphp/faker` | ^1.23 | Test data generation |
| `barryvdh/laravel-debugbar` | ^4.2 | Request profiling & debugging |
| `barryvdh/laravel-ide-helper` | ^3.7 | IDE auto-completion support |
| `laravel/pint` | ^1.27 | PHP code style fixer |
| `laravel/pail` | ^1.2.5 | Real-time log tailing |
| `laravel/pao` | ^1.0.6 | Development workflow |
| `mockery/mockery` | ^1.6 | Mocking for tests |
| `nunomaduro/collision` | ^8.6 | Beautiful error reporting |

---

## 🏗️ Architecture

Hotelia API follows a **Service Layer architecture** with clean separation of concerns:

```
HTTP Request → Middleware → Controller → Policy → Service → Model → Database
                                                      ↓
                                               Event → Listener → Notification
```

### Layer Responsibilities

| Layer | Purpose |
|---|---|
| **Controllers** | Thin - receive validated input, delegate to services, return JSON responses. Never contain business logic. |
| **Services** | Core business logic layer. Wrap operations in DB transactions. Dispatch events. |
| **Policies** | Object-level authorization. Enforce hotel-scoped ownership before mutations. |
| **Requests (FormRequest)** | Declarative input validation and authorization check, decoupled from controllers. |
| **Resources** | Transform Eloquent models into consistent, versioned JSON API representations. |
| **Events** | Domain-level state change signals (e.g., `BookingCreated`, `HousekeepingTaskUpdated`). |
| **Listeners** | React to events asynchronously - send notifications, trigger side effects. |
| **Notifications** | Structured in-app (database channel) messages delivered to relevant users. |
| **Models** | Eloquent entities - define relationships, casts, fillable fields, and boot hooks. |
| **Factories** | Realistic test data generation for all major entities. |
| **Seeders** | Deterministic database seeding: roles, permissions, super admin, and demo data. |
| **Tests** | Feature tests (full HTTP round-trips) and Unit tests (policy logic) with in-memory SQLite. |
| **Traits** | `LogsAuditTrail` - reusable behavior mixed into any Eloquent model. |
| **Constants** | Typed PHP classes/enums for statuses, permissions, roles, and notification types - no magic strings. |
| **Jobs** | Queued, schedulable background work (auto-cancel stale bookings, notify stuck rooms). |

---

## 📁 Folder Structure

```
hotelia-api/
├── app/
│   ├── Constants/                  # Typed constants and enums
│   │   ├── BookingStatus.php       # pending, confirmed, checked_in, checked_out, cancelled, no_show
│   │   ├── InvoiceStatus.php
│   │   ├── NotificationTypes.php
│   │   ├── PaymentStatus.php
│   │   ├── Permissions.php         # All 45 permission strings
│   │   ├── Roles.php               # Roles enum (super_admin, hotel_manager, etc.)
│   │   └── RoomStatus.php          # available, occupied, cleaning, maintenance, reserved
│   │
│   ├── Events/                     # Domain events by module
│   │   ├── Billing/                # InvoiceGenerated, InvoicePaid
│   │   ├── Bookings/               # BookingCreated, BookingCancelled, BookingCheckedIn, BookingCheckedOut, BookingUpdated
│   │   ├── Guests/                 # GuestCreated, GuestUpdated, GuestDeleted
│   │   ├── Hotels/                 # HotelCreated, HotelUpdated, HotelDeleted, HotelSettingUpdated
│   │   ├── Housekeeping/           # HousekeepingTaskCreated, HousekeepingTaskUpdated, HousekeepingTaskDeleted
│   │   ├── Maintenance/            # MaintenanceRequestCreated, MaintenanceRequestUpdated, MaintenanceRequestDeleted
│   │   ├── Rooms/                  # Room, RoomType, Amenity lifecycle events
│   │   └── Services/               # ServiceCreated, ServiceUpdated, ServiceDeleted
│   │
│   ├── Http/
│   │   ├── Controllers/Api/V1/     # Versioned API controllers by domain
│   │   │   ├── Admin/              # UserController (unlock), AuditController
│   │   │   ├── Auth/               # AuthController, PasswordResetController
│   │   │   ├── Billing/            # InvoiceController, PaymentController
│   │   │   ├── Bookings/           # BookingController
│   │   │   ├── Guests/             # GuestController
│   │   │   ├── Hotels/             # HotelController, HotelSettingController
│   │   │   ├── Housekeeping/       # HousekeepingController
│   │   │   ├── Maintenance/        # MaintenanceController
│   │   │   ├── Notifications/      # NotificationController
│   │   │   ├── Pricing/            # RatePlanController, PricingRuleController
│   │   │   ├── Reports/            # ReportController
│   │   │   ├── Rooms/              # RoomController, RoomTypeController, AmenityController, AvailabilityController
│   │   │   ├── Security/           # SecurityController (login history, failed logins)
│   │   │   ├── Services/           # ServiceController
│   │   │   └── Users/              # UserController
│   │   │
│   │   ├── Requests/               # FormRequest classes for validation
│   │   │   ├── Api/V1/             # Versioned request classes per domain
│   │   │   └── Auth/               # LoginRequest, ChangePasswordRequest, ForgotPasswordRequest, ResetPasswordRequest
│   │   │
│   │   └── Resources/Api/V1/       # API resource transformers per domain
│   │
│   ├── Jobs/
│   │   ├── AutoCancelStaleBookings.php     # Cancels pending bookings older than N minutes
│   │   └── NotifyStuckCleaningRooms.php    # Alerts staff about rooms stuck in cleaning
│   │
│   ├── Listeners/                  # Event listeners grouped by domain
│   │   ├── Billing/                # SendInvoiceNotification
│   │   ├── Bookings/               # SendBookingNotification, SendGuestBookingMailNotification
│   │   ├── Hotels/                 # LogHotelCreated, LogHotelUpdated, LogHotelDeleted, LogHotelSettingUpdated, NotifySuperAdmins*
│   │   ├── Housekeeping/           # SendHousekeepingTaskNotification
│   │   └── Maintenance/            # SendMaintenanceRequestNotification
│   │
│   ├── Mail/                       # Transactional mailable classes
│   │   ├── Billing/                # InvoiceGeneratedMail, InvoicePaidMail
│   │   └── Bookings/               # BookingConfirmationMail, BookingCancelledMail, BookingUpdatedMail, BookingNoShowMail
│   │
│   ├── Models/                     # Eloquent models
│   │   ├── Booking.php             # With BookingStatusHistory boot hook
│   │   ├── BookingRoom.php         # Pivot with price_per_night
│   │   ├── BookingService.php      # Pivot with quantity and price
│   │   ├── BookingStatusHistory.php
│   │   ├── FailedLoginAttempt.php
│   │   ├── Guest.php
│   │   ├── Hotel.php
│   │   ├── HotelSetting.php
│   │   ├── HousekeepingTask.php
│   │   ├── Invoice.php
│   │   ├── InvoiceItem.php
│   │   ├── LoginHistory.php
│   │   ├── MaintenanceRequest.php
│   │   ├── PasswordHistory.php
│   │   ├── Payment.php
│   │   ├── PricingRule.php
│   │   ├── RatePlan.php
│   │   ├── Room.php
│   │   ├── RoomStatusHistory.php
│   │   ├── RoomType.php
│   │   ├── Service.php
│   │   └── User.php
│   │
│   ├── Notifications/              # In-app notification classes
│   │   ├── BaseNotification.php
│   │   ├── Bookings/               # BookingNotification
│   │   ├── Hotels/                 # NewHotelCreatedNotification, HotelUpdatedNotification
│   │   ├── Housekeeping/           # HousekeepingTaskNotification, StuckInCleaningNotification
│   │   └── Maintenance/            # MaintenanceRequestNotification
│   │
│   ├── Policies/                   # Laravel policies for object-level auth
│   │   ├── AmenityPolicy.php
│   │   ├── BookingPolicy.php
│   │   ├── GuestPolicy.php
│   │   ├── HotelPolicy.php
│   │   ├── HotelSettingPolicy.php
│   │   ├── HousekeepingTaskPolicy.php
│   │   ├── InvoicePolicy.php
│   │   ├── MaintenanceRequestPolicy.php
│   │   ├── PaymentPolicy.php
│   │   ├── PricingRulePolicy.php
│   │   ├── RatePlanPolicy.php
│   │   ├── RoomPolicy.php
│   │   ├── RoomTypePolicy.php
│   │   ├── ServicePolicy.php
│   │   └── UserPolicy.php
│   │
│   ├── Providers/
│   │   └── AppServiceProvider.php  # Event bindings, rate limiter configuration
│   │
│   ├── Services/                   # Business logic layer
│   │   ├── Billing/                # BillingService (invoices, payments, reconciliation)
│   │   ├── Booking/                # BookingService (create, update, cancel, check-in/out)
│   │   ├── Guest/                  # GuestService
│   │   ├── Hotel/                  # HotelService, HotelSettingService, AncillaryService
│   │   ├── Housekeeping/           # HousekeepingService (task lifecycle + room status sync)
│   │   ├── Maintenance/            # MaintenanceService
│   │   ├── Pdf/                    # PdfService (invoice and receipt rendering)
│   │   ├── Pricing/                # PricingService (nightly rate calculation)
│   │   ├── Report/                 # ReportService (dashboard stats, revenue KPIs)
│   │   ├── Room/                   # RoomService, RoomTypeService, AmenityService, AvailabilityService
│   │   └── User/                   # UserService
│   │
│   └── Traits/
│       └── LogsAuditTrail.php      # Reusable Spatie activity log integration
│
├── database/
│   ├── factories/                  # Eloquent model factories for all entities
│   ├── migrations/                 # 34 ordered migrations
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── DemoDataSeeder.php
│       ├── RolesAndPermissionsSeeder.php
│       └── SuperAdminSeeder.php
│
├── routes/
│   ├── api.php                     # Main API entry point (v1 prefix + global throttle)
│   └── api/                        # Modular route files per domain
│       ├── admin.php               # Audit logs, user unlock
│       ├── auth.php                # Login, logout, password, token
│       ├── billing.php             # Invoices, payments
│       ├── bookings.php            # Booking CRUD + status transitions
│       ├── guests.php
│       ├── hotels.php              # Hotels + hotel settings
│       ├── housekeeping.php
│       ├── maintenance.php
│       ├── notifications.php
│       ├── pricing.php             # Rate plans & pricing rules
│       ├── reports.php             # Dashboard & revenue reports
│       ├── rooms.php               # Rooms, room types, amenities
│       ├── security.php            # Login history, failed logins
│       ├── services.php
│       └── users.php
│
└── tests/
    ├── ApiTestCase.php             # Shared API test case base
    ├── Feature/Api/V1/             # Full HTTP integration tests per domain
    ├── Feature/Jobs/               # Scheduled job tests
    ├── Feature/Performance/        # Caching performance tests
    ├── Traits/InteractsWithHotels.php
    ├── Unit/Policies/              # Policy unit tests
    └── Unit/Services/              # Service unit tests (e.g. PricingService)
```

---

## 🗄️ Database

The database is designed around a **multi-hotel tenancy** model, where every resource is scoped to a specific hotel.

### Entity Summary

| Table | Description |
|---|---|
| `users` | Staff accounts with soft deletes, lockout fields, password timestamps |
| `hotels` | Hotel profiles (name, slug, location, logo, active flag) |
| `hotel_settings` | Per-hotel configuration (currency, timezone, tax rate, fees, prefixes) |
| `hotel_user` | Pivot - assigns staff to hotels (many-to-many) |
| `room_types` | Room categories with base pricing and capacity per hotel |
| `rooms` | Physical rooms linked to a room type with real-time status |
| `amenities` | Global amenities catalog |
| `room_type_amenity` | Pivot - amenity assignments per room type |
| `guests` | Guest profiles (name, contact, nationality, ID info) |
| `bookings` | Booking records with reference, dates, occupancy counts, total amount, status |
| `booking_rooms` | Pivot - rooms per booking with price snapshot |
| `booking_services` | Pivot - ancillary services per booking with quantity and price snapshot |
| `booking_status_histories` | Immutable audit trail of every booking status change |
| `services` | Ancillary services per hotel (e.g., airport transfer, spa) |
| `rate_plans` | Named rate plans with modifiers, policies, and default flags |
| `pricing_rules` | Date/day/stay-based price rules scoped to hotel, rate plan, or room type |
| `invoices` | Auto-generated invoices per booking with tax computation |
| `invoice_items` | Line items: room stays + service charges |
| `payments` | Payment transactions per booking (method, amount, status, reference) |
| `housekeeping_tasks` | Room cleaning tasks with status and completion timestamps |
| `maintenance_requests` | Room maintenance requests with priority and status |
| `room_status_histories` | Audit log of room status changes |
| `login_histories` | Per-session login/logout records (IP, user agent) |
| `failed_login_attempts` | Brute-force detection log |
| `password_histories` | Last-N password hashes for reuse prevention |
| `notifications` | Laravel database-channel notifications |
| `personal_access_tokens` | Sanctum API tokens |
| `activity_log` | Spatie audit trail |

### Key Relationships

```
Hotel ──< RoomType ──< Room ──< HousekeepingTask
                              └──< MaintenanceRequest
Hotel ──< Booking ──< BookingRoom >── Room
                  └──< BookingService >── Service
                  └──< Invoice ──< InvoiceItem
                  └──< Payment
                  └──> RatePlan (optional)
Hotel ──< RatePlan ──< PricingRule
Hotel >──< User (via hotel_user pivot)
RoomType >──< Amenity (via room_type_amenity pivot)
```

### Multi-Hotel Support

- Every resource (rooms, bookings, services, housekeeping, maintenance) is scoped to a `hotel_id`
- Laravel's `scopeBindings()` enforces hierarchical route model binding - a booking can only be resolved if it belongs to the hotel in the URL
- Policies enforce that the authenticated user belongs to the target hotel before any mutation
- The `hotel_user` pivot allows staff to be assigned to multiple hotels

### Performance Indexes

A dedicated migration adds the following indexes for query performance:

- `bookings(hotel_id, status)` - status-filtered booking queries
- `bookings(check_in_date, check_out_date)` - date range availability checks
- `bookings(hotel_id, status, check_in_date, check_out_date)` - compound tenant availability index
- `housekeeping_tasks(room_id, status)` - room-specific task filtering
- `maintenance_requests(room_id, status)` - room-specific maintenance filtering
- `payments(booking_id, status)` - payment reconciliation queries
- `rooms(hotel_id, room_number)` UNIQUE - prevents duplicate room numbers per hotel

---

## 🌐 API

### Base URL

```
/api/v1
```

### Versioning

The API is versioned at the URL level (`/api/v1/`). All controllers, requests, and resources are namespaced under `Api/V1/` to allow parallel version development.

### Authentication

All protected routes require a Bearer token in the `Authorization` header:

```http
Authorization: Bearer <sanctum-token>
```

Tokens are issued on login and revoked on logout or password change.

### Route Overview

| Module | Base Path | Actions |
|---|---|---|
| Authentication | `/api/v1/auth` | login, logout, logout-all, me, status, refresh-token, change-password, forgot-password, reset-password |
| Hotels | `/api/v1/hotels` | CRUD |
| Hotel Settings | `/api/v1/hotels/{hotel}/settings` | show, update |
| Room Types | `/api/v1/hotels/{hotel}/room-types` | CRUD |
| Rooms | `/api/v1/hotels/{hotel}/rooms` | CRUD |
| Availability | `/api/v1/hotels/{hotel}/availability` | index (date-range matrix with pricing) |
| Amenities | `/api/v1/amenities` | index, store, update, destroy |
| Rate Plans | `/api/v1/hotels/{hotel}/rate-plans` | CRUD |
| Pricing Rules | `/api/v1/hotels/{hotel}/pricing-rules` | CRUD |
| Guests | `/api/v1/hotels/{hotel}/guests` | CRUD |
| Services | `/api/v1/hotels/{hotel}/services` | CRUD |
| Bookings | `/api/v1/hotels/{hotel}/bookings` | CRUD + cancel, check-in, check-out |
| Invoices | `/api/v1/hotels/{hotel}/bookings/{booking}/invoice` | show, regenerate, PDF download |
| Payments | `/api/v1/hotels/{hotel}/bookings/{booking}/payments` | index, store, update status, receipt PDF |
| Housekeeping | `/api/v1/hotels/{hotel}/housekeeping` | CRUD |
| Maintenance | `/api/v1/hotels/{hotel}/maintenance` | CRUD |
| Notifications | `/api/v1/notifications` | index, mark-as-read |
| Reports | `/api/v1/hotels/{hotel}/reports` | dashboard, revenue |
| Security | `/api/v1/login-history`, `/api/v1/failed-logins` | index |
| Admin | `/api/v1/admin` | audit-logs, user unlock |
| Users | `/api/v1/users` | CRUD |

### Validation

All write operations use dedicated `FormRequest` classes with Laravel's built-in validation. Validation errors are returned as standard 422 responses.

### Filtering, Sorting & Pagination

Routes that support it use **Spatie Laravel Query Builder** - clients can pass `filter[field]=value`, `sort=field` or `sort=-field` (descending), and `page[number]=1&page[size]=15` query parameters.

### Error Handling

| HTTP Code | Meaning |
|---|---|
| `200` | Success |
| `201` | Resource created |
| `401` | Unauthenticated |
| `403` | Forbidden (policy denied) |
| `404` | Resource not found |
| `422` | Validation failed |
| `423` | Account locked |
| `429` | Too many requests (rate limited) |
| `500` | Server error |

### Response Structure

All responses follow a consistent envelope:

```json
{
  "success": true,
  "message": "Bookings Retrieved Successfully.",
  "data": { ... }
}
```

---

## 🔒 Security

| Feature | Implementation |
|---|---|
| **API Authentication** | Laravel Sanctum - stateless Bearer tokens |
| **Role-Based Access Control** | Spatie Permission - 5 roles, 45 permissions |
| **Object-Level Authorization** | Laravel Policies - hotel-scoped ownership enforced on every mutation |
| **Cross-Tenant Isolation** | `scopeBindings()` on all nested hotel routes |
| **Account Lockout** | Auto-lock after 3 failed attempts, 15-minute cooldown |
| **Rate Limiting** | 5/min (login), 10/min (sensitive ops), 60/min (global API) - per IP or user ID |
| **Password History** | Last 5 password hashes stored - reuse blocked |
| **Session Revocation** | All other tokens revoked on password change |
| **Audit Logging** | Spatie Activity Log on all major models - only dirty fields recorded |
| **Failed Login Tracking** | IP address, user agent, and timestamp logged per failed attempt |
| **Login History** | Per-session login/logout tracking |
| **Input Validation** | All inputs validated via FormRequest before hitting any service layer |
| **Soft Deletes** | All major entities use soft deletes - no permanent data loss |

---

## 📡 Events & Listeners

Events are registered in `AppServiceProvider::boot()`.

| Event | Listener | Purpose |
|---|---|---|
| `BookingCreated` | `SendBookingNotification` | Notifies relevant hotel staff of new booking |
| `BookingCreated` | `SendGuestBookingMailNotification` | Sends booking confirmation email to guest |
| `BookingUpdated` | `SendBookingNotification` | Notifies staff of booking changes |
| `BookingUpdated` | `SendGuestBookingMailNotification` | Sends update or no-show email to guest |
| `BookingCancelled` | `SendBookingNotification` | Notifies staff of cancellation |
| `BookingCancelled` | `SendGuestBookingMailNotification` | Sends cancellation email to guest |
| `BookingCheckedIn` | `SendBookingNotification` | Notifies staff of guest check-in |
| `BookingCheckedOut` | `SendBookingNotification` | Notifies staff of guest check-out |
| `InvoiceGenerated` | `SendInvoiceNotification` | Sends invoice email to guest |
| `InvoicePaid` | `SendInvoiceNotification` | Sends paid-invoice email to guest |
| `HousekeepingTaskCreated` | `SendHousekeepingTaskNotification` | Notifies housekeeping team of new task |
| `HousekeepingTaskUpdated` | `SendHousekeepingTaskNotification` | Notifies on task status changes |
| `HousekeepingTaskDeleted` | `SendHousekeepingTaskNotification` | Notifies on task deletion |
| `MaintenanceRequestCreated` | `SendMaintenanceRequestNotification` | Notifies maintenance staff of new request |
| `MaintenanceRequestUpdated` | `SendMaintenanceRequestNotification` | Notifies on maintenance status changes |
| `MaintenanceRequestDeleted` | `SendMaintenanceRequestNotification` | Notifies on maintenance request deletion |

Additional events exist for hotels, guests, rooms, room types, amenities, and services — these are dispatched for activity-log observability and future extensibility, but do not currently have active listeners beyond audit logging.

---

## 🔔 Notifications

### In-App (Database Channel)

All in-app notifications use the **database channel** (stored in `notifications` table) and extend `BaseNotification`.

| Notification | Trigger | Recipients |
|---|---|---|
| `BookingNotification` | Booking created, cancelled, checked-in, checked-out | Hotel staff |
| `NewHotelCreatedNotification` | Hotel created | Super admins |
| `HotelUpdatedNotification` | Hotel updated | Super admins |
| `HousekeepingTaskNotification` | Housekeeping task created or updated | Housekeeping-permissioned staff |
| `StuckInCleaningNotification` | Room stuck in `cleaning` status for > N minutes | Housekeeping-permissioned staff (with cooldown deduplication via cache) |
| `MaintenanceRequestNotification` | Maintenance request created | Maintenance-permissioned staff |

### Email (SMTP)

Transactional emails are sent synchronously via Laravel Mail when SMTP is configured. See [Transactional Email](#-transactional-email) under Features.

| Mailable | Trigger | Recipient |
|---|---|---|
| `BookingConfirmationMail` | Booking created | Guest |
| `BookingUpdatedMail` | Booking updated | Guest |
| `BookingCancelledMail` | Booking cancelled | Guest |
| `BookingNoShowMail` | Booking marked no-show | Guest |
| `InvoiceGeneratedMail` | Invoice generated | Guest |
| `InvoicePaidMail` | Invoice fully paid | Guest |

---

## ⚙️ Queues & Scheduled Jobs

### Queue Worker

```bash
php artisan queue:listen --tries=1 --timeout=0
```

Both jobs implement `ShouldQueue` with 3 retry attempts and exponential backoff (10s, 30s, 60s).

### Scheduled Jobs

Defined in `routes/console.php` - both jobs run every 15 minutes:

| Job | Schedule | Purpose |
|---|---|---|
| `AutoCancelStaleBookings` | Every 15 minutes | Cancels `pending` bookings older than `BOOKING_AUTO_CANCEL_MINUTES` (default: 120 min) |
| `NotifyStuckCleaningRooms` | Every 15 minutes | Notifies staff of rooms stuck in `cleaning` status for > `ROOM_CLEANING_ALERT_MINUTES` (default: 120 min) - with cache-based deduplication to prevent spam |

### Scheduler

Start the Laravel task scheduler (typically via cron):

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🚀 Installation

### Prerequisites

- PHP 8.3+
- Composer 2.x
- Node.js 18+ (for Vite/npm scripts)
- MySQL / PostgreSQL (or SQLite for local development)
- A queue worker (database driver works out of the box)

### Steps

**1. Clone the repository**

```bash
git clone https://github.com/your-org/hotelia-api.git
cd hotelia-api
```

**2. Install PHP dependencies**

```bash
composer install
```

**3. Copy environment file**

```bash
cp .env.example .env
```

**4. Generate application key**

```bash
php artisan key:generate
```

**5. Configure database**

Edit `.env` with your database credentials:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hotelia
DB_USERNAME=root
DB_PASSWORD=secret
```

**6. Run database migrations**

```bash
php artisan migrate
```

**7. Seed the database**

```bash
# Seed roles, permissions, and super admin account
php artisan db:seed

# Optionally seed demo data
php artisan db:seed --class=DemoDataSeeder
```

**8. Create storage symlink**

```bash
php artisan storage:link
```

**9. Generate Swagger documentation**

```bash
php artisan l5-swagger:generate
```

**10. Start the development server**

```bash
# Start all services concurrently (server + queue + logs + vite)
composer run dev
```

Or individually:

```bash
php artisan serve           # API server
php artisan queue:listen    # Queue worker
php artisan schedule:work   # Task scheduler (dev only)
```

---

## 🌍 Environment Variables

| Variable | Default | Description |
|---|---|---|
| `APP_NAME` | `hotelia-api` | Application name |
| `APP_ENV` | `local` | Environment (`local`, `production`) |
| `APP_KEY` | - | Application encryption key (set via `key:generate`) |
| `APP_DEBUG` | `true` | Enable debug mode |
| `APP_URL` | `http://localhost` | Base application URL |
| `DB_CONNECTION` | `sqlite` | Database driver |
| `DB_HOST` | `127.0.0.1` | Database host |
| `DB_PORT` | `3306` | Database port |
| `DB_DATABASE` | `hotelia` | Database name |
| `DB_USERNAME` | `root` | Database username |
| `DB_PASSWORD` | - | Database password |
| `QUEUE_CONNECTION` | `database` | Queue driver (`database`, `redis`, `sync`) |
| `CACHE_STORE` | `database` | Cache driver (`database`, `redis`, `array`) |
| `MAIL_MAILER` | `smtp` | Mail driver for password reset emails |
| `MAIL_HOST` | - | SMTP host |
| `MAIL_PORT` | `587` | SMTP port |
| `MAIL_USERNAME` | - | SMTP username |
| `MAIL_PASSWORD` | - | SMTP password |
| `MAIL_FROM_ADDRESS` | - | Sender email address |
| `FRONTEND_URL` | `http://localhost:3000` | Frontend URL for password reset links |
| `BOOKING_AUTO_CANCEL_MINUTES` | `120` | Pending booking stale threshold (minutes) |
| `ROOM_CLEANING_ALERT_MINUTES` | `120` | Room stuck-in-cleaning threshold (minutes) |
| `L5_SWAGGER_GENERATE_ALWAYS` | `false` | Auto-regenerate Swagger docs on each request |

---

## 🧪 Running Tests

Tests use an **in-memory SQLite database** and run isolated — no external services required.

### Run All Tests

```bash
composer run test
# or
php artisan test
```

### Run Unit Tests Only

```bash
php artisan test --testsuite=Unit
```

### Run Feature Tests Only

```bash
php artisan test --testsuite=Feature
```

### Run a Specific Test File

```bash
php artisan test tests/Feature/Api/V1/Bookings/BookingTest.php
```

### Run a Specific Test Method

```bash
php artisan test --filter test_can_create_booking
```

### Run with Coverage Report

```bash
php artisan test --coverage
# or with HTML report
php artisan test --coverage-html coverage/
```

### Test Suites

| Suite | Location | Coverage |
|---|---|---|
| **Feature / Auth** | `tests/Feature/Api/V1/Auth/` | Login, logout, password change, token refresh |
| **Feature / Hotels** | `tests/Feature/Api/V1/Hotels/` | Hotel CRUD, settings |
| **Feature / Rooms** | `tests/Feature/Api/V1/Rooms/` | Rooms, room types, amenities, availability |
| **Feature / Bookings** | `tests/Feature/Api/V1/Bookings/` | Full booking lifecycle, email notifications |
| **Feature / Billing** | `tests/Feature/Api/V1/Billing/` | Invoices, payments, PDF, email notifications |
| **Feature / Pricing** | `tests/Feature/Api/V1/Pricing/` | Rate plans, pricing rules |
| **Feature / Guests** | `tests/Feature/Api/V1/Guests/` | Guest management |
| **Feature / Housekeeping** | `tests/Feature/Api/V1/Housekeeping/` | Task lifecycle + checkout integration |
| **Feature / Maintenance** | `tests/Feature/Api/V1/Maintenance/` | Maintenance request CRUD |
| **Feature / Notifications** | `tests/Feature/Api/V1/Notifications/` | Notification delivery |
| **Feature / Reports** | `tests/Feature/Api/V1/Reports/` | Dashboard stats, revenue KPIs |
| **Feature / Security** | `tests/Feature/Api/V1/Security/` | Login history, failed logins, cross-tenant tests |
| **Feature / Services** | `tests/Feature/Api/V1/Services/` | Ancillary services |
| **Feature / Users** | `tests/Feature/Api/V1/Users/` | User management |
| **Feature / Jobs** | `tests/Feature/Jobs/` | AutoCancelStaleBookings, NotifyStuckCleaningRooms |
| **Feature / Performance** | `tests/Feature/Performance/` | Report caching behavior |
| **Unit / Policies** | `tests/Unit/Policies/` | Booking, Room, RoomType, Service, Housekeeping, Maintenance, HotelSetting, RatePlan, PricingRule policies |
| **Unit / Services** | `tests/Unit/Services/` | PricingService nightly rate calculation |

---

## 📖 API Documentation

Hotelia API uses **L5-Swagger** (OpenAPI 3.0) with PHP 8 attribute-based annotations directly on controller methods.

### Generate Documentation

```bash
php artisan l5-swagger:generate
```

### Access Swagger UI

After starting the server:

```
http://localhost:8000/api/documentation
```

### Auto-Generation (Development)

Set in `.env` to regenerate on every request:

```dotenv
L5_SWAGGER_GENERATE_ALWAYS=true
```

> **Note:** Disable `L5_SWAGGER_GENERATE_ALWAYS` in production for performance.

---

## 🔄 Development Workflow

### Feature Branch Strategy

```bash
git checkout -b feature/your-feature-name
# develop, test, commit
git push origin feature/your-feature-name
# open pull request
```

### Adding a New Resource Module

Follow this order to maintain architectural consistency:

1. **Migration** - define the database schema
2. **Model** - relationships, casts, fillable, `LogsAuditTrail` trait
3. **Factory** - realistic fake data for tests
4. **Seeder** - add to `DemoDataSeeder` if applicable
5. **Events** - `ResourceCreated`, `ResourceUpdated`, `ResourceDeleted`
6. **Service** - business logic wrapped in `DB::transaction()`, dispatch events
7. **Requests** - `StoreXRequest`, `UpdateXRequest` with validation rules
8. **Policy** - hotel-scoped `viewAny`, `view`, `create`, `update`, `delete`
9. **Controller** - thin; delegate to service, authorize via policy, return resource
10. **Resource** - JSON transformer
11. **Routes** - add to `routes/api/module.php`, register in `routes/api.php`
12. **Listener** (if needed) - notifications, side effects
13. **Tests** - feature test covering all endpoints + unit test for policy

### Code Style

```bash
./vendor/bin/pint        # Fix code style
./vendor/bin/pint --test # Check without fixing
```

### IDE Helper (Autocomplete)

```bash
php artisan ide-helper:generate
php artisan ide-helper:models
```

---

## 🗺️ Roadmap

### ✅ Implemented

| Module | Description |
|---|---|
| **Rate Plans** | Named plans (BAR, corporate, group) with modifiers attached to bookings |
| **Dynamic Pricing Engine** | Date-range, day-of-week, and stay-length pricing rules with priority ordering |
| **Room Availability Matrix** | Date-range availability query with per-night pricing |
| **Transactional Email** | Guest booking and invoice emails via SMTP (requires mail configuration) |
| **PDF Documents** | Invoice and payment receipt PDF downloads |

### 🔲 Planned

| Module | Description |
|---|---|
| **Admin UI** | Web front desk dashboard for hotel staff |
| **Channel Management** | OTA integration (Booking.com, Expedia) via channel manager adapter |
| **Online Payments** | Payment gateway integration (Stripe, Flutterwave, M-Pesa Daraja API) |
| **Guest Portal API** | Self-service endpoints for guests to view bookings and invoices |
| **Bulk Operations** | Bulk check-in, bulk housekeeping task assignment |
| **Webhook System** | Outbound webhooks for third-party integrations |
| **Multi-Currency Billing** | Invoice generation with FX conversion beyond hotel-configured currency |
| **Staff Scheduling** | Shift management for housekeeping and maintenance staff |
| **Queued Email Delivery** | Move transactional mail to queued jobs for production reliability |
| **CI/CD & Deployment** | Docker, GitHub Actions, staging/production runbooks |

---

## 🤝 Contributing

Contributions are welcome! Please follow these guidelines:

1. **Fork** the repository and create your feature branch from `main`
2. **Write tests** - all new features must include corresponding feature tests
3. **Follow the architecture** - use the Service Layer pattern; keep controllers thin
4. **Use constants** - never use magic strings for statuses, roles, or permissions
5. **Document your API** - annotate new controller methods with OpenAPI attributes
6. **Run the test suite** before opening a pull request: `composer run test`
7. **Format your code**: `./vendor/bin/pint`
8. **Write descriptive commit messages** following conventional commits format

### Pull Request Checklist

- [ ] Tests pass (`composer run test`)
- [ ] Code style clean (`./vendor/bin/pint --test`)
- [ ] New endpoints annotated with OpenAPI attributes
- [ ] Migration included for schema changes
- [ ] Policy updated or created for new resource
- [ ] Seeder updated if new permissions were added

---

## 📄 License

This project is licensed under the **MIT License**. See the [LICENSE](LICENSE) file for details.

---

## 👤 Author

**Brian Mulindi**
*Senior Software Engineer - Laravel & API Architecture*

- 🌍 Nairobi, Kenya
- 💼 Specializing in commercial-grade Laravel backends, RESTful API design, and clean architecture
- 🏨 Hotelia API - built with a focus on scalability, security, and maintainability

---

<div align="center">

Built with ❤️ using [Laravel](https://laravel.com) · [Spatie](https://spatie.be) · [L5-Swagger](https://github.com/DarkaOnLine/L5-Swagger) · [DomPDF](https://github.com/barryvdh/laravel-dompdf)

</div>
