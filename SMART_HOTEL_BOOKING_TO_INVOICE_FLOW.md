# SMART HOTEL BOOKING TO INVOICE FLOW

This report is based on a direct source-code trace of the current Laravel project. No application code was modified, deleted, or refactored while preparing this report. This file is the documentation artifact only.

---

## 1. Executive summary

The booking lifecycle in this project is not a single automatic “create booking -> invoice” pipeline.

From the actual code:
- The user starts from the bookings page and creates a booking via `BookingController::store`.
- Booking creation is delegated to `BookingService::createSingleBooking`.
- The service validates guest duplication, room availability, and pricing, then writes the booking, guest, additional-service, room-allocation, and payment records.
- Check-in is done separately through `BookingController::checkIn` and updates room occupancy plus the booking status.
- Checkout is done separately through `BookingController::checkOut` and sets `actual_check_out_at` and `status = 'checked_out'`.
- The invoice is not created automatically during booking creation in the live code path; the actual invoice record is created later when the user opens the invoice generation page and submits the form to `InvoiceController::store`.
- Final invoice display is rendered through the Blade invoice template and converted to PDF on the browser using `html2canvas` + `jsPDF`.

This means the real flow is: booking creation -> room allocation -> check-in/out -> manual invoice generation -> invoice record save -> invoice render/download.

---

## 2. Route entry points

The primary route definitions live in `routes/web.php`.

Relevant booking routes:
- `Route::resource('bookings', BookingController::class)`
- `POST /booking/check-in` -> `BookingController::checkIn`
- `POST /bookings/{id}/checkout` -> `BookingController::checkOut`
- `GET /booking/get-available-rooms/{bookingId}` -> `BookingController::getAvailableRooms`
- `POST /bookings/{booking}/cancel` -> `BookingController::cancel`

Relevant invoice routes:
- `GET /invoice-management/generate-invoice` -> `InvoiceController::generate`
- `POST /invoice-management/generate-invoice` -> `InvoiceController::store`
- `GET /invoice-management/generate-invoice/{id}/invoice-data` -> `InvoiceController::getInvoiceData`
- `GET /invoice-management/invoices/{invoice}/download` -> `InvoiceController::download`

Important finding: the UI exposes a “Generate Invoice” action from the booking list, but the invoice is a separate concern from the booking itself. There is no direct route that auto-generates an invoice immediately after booking creation in the current path.

---

## 3. Booking UI flow

The main UI is `resources/views/pages/bookings/index.blade.php`.

This page:
- lists bookings and filter controls,
- has a “Create” modal,
- has a “Generate Invoice” button,
- displays action buttons for check-in / checkout / invoice generation.

The booking create modal is likely in `resources/views/components/create-booking-modal.blade.php`, and the check-in modal is in `resources/views/components/checkin-modal.blade.php`.

UI behavior in JS:
- `Create Booking` submits the booking form to the booking controller.
- Availability is validated in the browser and server-side before booking is accepted.
- The booking result is returned as JSON with `status`, `booking_id`, `booking_code`, `total_amount`.

The invoice list page is `resources/views/pages/invoiceManagement/generateInvoice/index.blade.php`, which performs the following:
- fetches booking invoice data via `GET /invoice-management/generate-invoice/{bookingId}/invoice-data`,
- builds the invoice line items in the modal,
- calculates subtotal, GST, and total in JavaScript,
- posts the final invoice payload to `POST /invoice-management/generate-invoice`.

---

## 4. Booking creation path

### 4.1 Controller entry: `BookingController::store`

In `app/Http/Controllers/BookingController.php`, `store()` does the following:
- validates request inputs,
- normalizes `payment` data,
- resolves hotel-specific allowed rate types via `Hotel::getBookingRateOptions()`,
- validates `guest`, `agent`, `additional_services`, and `payment` arrays,
- builds `$bookingData`,
- calls `BookingService::createSingleBooking($bookingData)`.

This controller returns a JSON response on success/failure and is designed for AJAX form submission.

### 4.2 Booking domain service: `BookingService::createSingleBooking`

This is the core entry point for single bookings.

The service:
1. Normalizes payment payload.
2. Loads the hotel and resolves the booking rate type (`room` or `hourly`).
3. Calls `validateGuestNotAlreadyCheckedIn()` to block duplicate active check-ins for the same mobile number in the same hotel.
4. Calls `AvailabilityService::validateAvailability()` for room availability.
5. Calls `PricingService::calculateBookingPrice()` to compute the base price + tax + total.
6. Calculates additional service charges via `PricingService::calculateServiceCharges()`.
7. Generates a booking code.
8. Creates the booking row.
9. Creates guest rows and `booking_guests` records.
10. Allocates physical room numbers via `RoomAllocationService::allocateRooms()`.
11. Saves booking additional service rows via `BookingAdditionalService::create()`.
12. Saves payment rows if paid amount exists.
13. Updates booking status to `confirmed`.
14. Calls the helper `auditLog(...)` to queue an `AuditLogJob`.

Important direct observation: the commented-out block in this function shows that invoice creation was considered there, but in the current active logic it is not executed. The actual invoice is created elsewhere.

---

## 5. Availability validation and room allocation

### 5.1 AvailabilityService

`app/Services/AvailabilityService.php` performs the actual availability checks.

Key logic:
- It parses check-in and check-out dates.
- It counts available room numbers for a hotel + room type with status `0` (available only).
- It excludes rooms that are already allocated to bookings with statuses: `pending`, `confirmed`, `checked_in`.
- It checks the date range by `allocation_date` in `room_distributions`.

This is crucial because room availability is not just based on `RoomNumber` status; it is also blocked via `room_distributions` records.

### 5.2 RoomAllocationService

`RoomAllocationService::allocateRooms()` does the actual assignment.

For each required room:
- it finds available rooms in a transaction,
- locks rows for update,
- ensures no room conflicts during the stay period,
- creates one `room_distributions` row per night (one row per room per date),
- records the `room_number_id`, `room_type_id`, `hotel_id`, `booking_id`, and `allocation_date`.

This is the real “room assignment” engine behind the booking.

---

## 6. Pricing logic

The main pricing engine is `app/Services/PricingService.php`.

### 6.1 Room pricing

When `rate_type` is not hourly, `calculateBookingPrice()`:
- loads the hotel and room type,
- validates the room type belongs to the hotel,
- loops through each night between check-in and check-out,
- fetches the relevant daily rate using `getRateForDate()`,
- includes seasonal and weekend pricing if configured,
- adds extra guest charges,
- applies discounts,
- calculates GST using `calculateTaxes()`,
- returns `base_amount`, `discount_amount`, `cgst`, `sgst`, `igst`, `total_amount`, plus daily breakdown.

### 6.2 Hourly pricing

For hourly bookings, `calculateBookingPrice()` uses `HourlyRate` records instead of nightly room rates.

`calculateActualHourlyCharge()` is used during checkout for actual billed duration based on:
- `actual_check_in_at`
- `actual_check_out_at`
- `HourlyRate::base_hours`
- `HourlyRate::extra_hour_price`
- `BookingAdditionalService` service amounts

This is the logic that drives the final billed amount in hourly booking cases.

### 6.3 Taxes

`PricingService::calculateTaxes()` computes GST splits based on hotel configuration:
- `is_gst_applicable`
- `invoice_tax_type` (`inclusive` / `exclusive`)
- `cgst_percentage`
- `sgst_percentage`
- `igst_percentage`

The configured hotel tax setup is later snapshotted into the invoice record in `InvoiceController::store`.

---

## 7. Data created at booking time

The current booking creation writes the following records:

### 7.1 Booking

`bookings` table stores:
- hotel id,
- booking code / booking number,
- booking type (`regular`, `bulk`, `hourly`),
- check-in/out dates,
- actual check-in/out timestamps,
- room count,
- base amount,
- discount,
- tax amounts,
- total_amount,
- status

From the migration file `2026_01_14_100709_create_bookings_table.php`, the table originally had `guest_id` and later migrations removed it; the current app uses `booking_guests` rather than a direct `bookings.guest_id` relationship.

### 7.2 Guests

`booking_guests` are created in `booking_guests` table and store:
- booking_id,
- guest_id (nullable),
- full_name,
- mobile,
- email,
- address,
- dob,
- country_id,
- state_id,
- is_primary_guest.

This is the current source of guest record linkage for a booking.

### 7.3 Room allocation

`room_distributions` stores one row per room number per night:
- `booking_id`
- `room_number_id`
- `room_type_id`
- `hotel_id`
- `allocation_date`
- `is_room_assigned`

The unique constraint ensures each room/date combination is not duplicated for the same hotel + room type.

### 7.4 Additional services

`booking_additional_services` stores the booking’s chosen add-ons, including:
- `booking_id`
- `additional_service_id`
- `quantity`
- `amount`

Note: the migration names show some schema drift (`booking_additional_servives` renamed to `booking_additional_services`), but the actual application code uses the final table name in the model relationships.

### 7.5 Payment

`booking_payments` records any payment collected at booking time.

The booking payment row includes:
- `booking_id`
- `hotel_id`
- `method`
- `amount`
- `paid_at`
- `transaction_reference`
- `status`
- `collected_by`

---

## 8. Booking status lifecycle

The booking lifecycle in code covers these state transitions:

- `pending` after initial booking creation (in `BookingService::createSingleBooking`, it creates booking then immediately updates to `confirmed`)
- `confirmed` after booking creation
- `checked_in` after `BookingController::checkIn`
- `checked_out` after `BookingController::checkOut`
- `cancelled` / `no_show` via dedicated actions

The actual check-in logic:
- validates selected rooms,
- ensures they belong to the hotel and room type,
- ensures the room is available (status `0`),
- updates `RoomNumber::status` to `1` (occupied),
- stores `actual_check_in_at`,
- updates booking `status` to `checked_in`.

The actual checkout logic:
- validates that guest was checked in and not already checked out,
- accepts custom `checkout_date` + `checkout_time` from the invoice/generate page,
- if hourly booking, recalculates actual charge via `PricingService::calculateActualHourlyCharge()`,
- updates `actual_check_out_at`,
- updates booking `status` to `checked_out`,
- marks assigned room numbers to `status = 3` (`dirty`) for housekeeping.

---

## 9. Invoice generation path

The invoice generation page is `resources/views/pages/invoiceManagement/generateInvoice/index.blade.php`.

The user opens the invoice-generation modal via a booking row action. The JS calls:
- `GET /invoice-management/generate-invoice/{bookingId}/invoice-data`

### 9.1 `InvoiceController::getInvoiceData`

This method loads the booking with related guests, room allocations, additional services, and invoice.

It calculates:
- `room_price` based on booking base amount or hourly breakdown,
- `extra_room_charge` for hourly extra hours,
- `additional_services` from `booking->additionalServices`,
- `hotel_state_id` and `guest_state_id` for GST type selection,
- `invoice_id` if an invoice already exists.

The JSON response contains the booking’s customer data and pricing metadata used to fill the invoice form.

### 9.2 JS invoice form logic

The JavaScript in `index.blade.php` builds invoice rows dynamically:
- room row,
- extra-hour row (for hourly bookings),
- service rows for additional services,
- taxable / GST / CGST / SGST / total calculations.

The final invoice form posts to `POST /invoice-management/generate-invoice`.

---

## 10. Invoice save logic

`InvoiceController::store()` is the decisive invoice-creation method.

### 10.1 Validation

The request validates:
- `booking_id`
- `name`
- `email`
- `invoice_date`
- line items array
- totals (`grand_total`, `total_taxable`, `total_gst`, `balance_amount`)

### 10.2 Invoice record creation

It uses:
- `Invoice::updateOrCreate(['booking_id' => $booking->id], [...])`

It stores the invoice snapshot fields:
- `hotel_id`
- `booking_id`
- `invoice_number`
- `is_gst_applicable`
- `invoice_tax_type`
- `cgst_percentage`
- `sgst_percentage`
- `igst_percentage`
- `room_amount`
- `service_amount`
- `subtotal`
- `cgst_amount`
- `sgst_amount`
- `igst_amount`
- `total_tax`
- `grand_total`
- `invoice_date`

The invoice number is generated by `generateInvoiceNumber($hotelId)` using hotel prefix and the latest invoice number in the same hotel.

### 10.3 Invoice item rows

Each item in the request is iterated and saved to `invoice_items`:
- `item_type`
- `item_name`
- `quantity`
- `price`
- `total`

The code groups totals by item type:
- room items increment `room_amount`
- `extra` or `service` increment `service_amount`

It also syncs booking additional services back to the booking if there are matching service IDs.

### 10.4 Related entity update

After saving the invoice, it updates the invoice totals and also syncs selected additional service rows to the booking relation.

The result is a JSON response:
- `status`: true,
- `message`: `Invoice saved successfully.`
- `data.invoice_id`

---

## 11. Invoice download & display

### 11.1 Download endpoint

`InvoiceController::download(Invoice $invoice)` loads:
- the booking,
- the primary guest,
- hotel info,
- all invoice items,
- subtotal/tax/grand total,

Then it returns:
- `view('invoices.master-invoice1.invoice-1', compact('viewData'))`

### 11.2 Blade invoice template

The invoice is rendered in `resources/views/invoices/master-invoice1/invoice-1.blade.php`.

It reads from `$viewData` and renders:
- hotel letterhead,
- invoice no/date,
- guest info,
- booking summary,
- itemized invoice table,
- subtotal / tax summary,
- grand total,
- footer note.

The template calculates GST line items and prints totals in INR format.

### 11.3 PDF generation

The layout includes the scripts:
- `public/assets/invoice/assets-1/js/jquery.min.js`
- `public/assets/invoice/assets-1/js/jspdf.min.js`
- `public/assets/invoice/assets-1/js/html2canvas.min.js`
- `public/assets/invoice/assets-1/js/custom.js`

The actual PDF logic is in `public/assets/invoice/assets-1/js/custom.js`:
- it captures the invoice section with `html2canvas`,
- converts to JPEG data URL,
- creates a `jsPDF` document,
- saves as `digital-invoico.pdf`.

This confirms the invoice is converted to PDF in the browser, not directly server-generated by a dedicated PDF service for this flow.

---

## 12. Database schema summary

Relevant schema files inspected:
- `database/migrations/2026_01_14_100709_create_bookings_table.php`
- `database/migrations/2026_01_14_100726_create_room_distributions_table.php`
- `database/migrations/2026_03_07_081750_create_booking_guests_table.php`
- `database/migrations/2026_03_06_112737_create_invoices_table.php`
- `database/migrations/2026_03_06_112809_create_invoice_items_table.php`
- `database/migrations/2026_02_07_114203_create_booking_additional_servives_table.php`
- `database/migrations/2026_03_04_134943_rename_booking_additional_servives_table.php`

Important schema features:
- `bookings` is the master reservation record.
- `booking_guests` is the actual guest collection model used by the app.
- `room_distributions` holds nightly room allocation blocks.
- `invoices` stores the final bill snapshot.
- `invoice_items` stores line items per invoice.

--

## 13. Eloquent relationships (actual application model logic)

From the models:

- `Booking` has:
  - `roomDistributions()`
  - `invoices()` / `invoice()`
  - `primaryGuest()`
  - `bookingGuests()`
  - `additionalServices()`
  - `payments()`
  - `guests()`
  - `hotel()`

- `BookingGuest` has:
  - `booking()`
  - `idProofs()`
  - `guest()`
  - `country()`
  - `state()`

- `Invoice` has:
  - `items()`
  - `booking()`

- `Hotel` exposes booking-rate configuration via `supportsRoomRate()`, `supportsHourlyRate()`, and `getDefaultBookingRateType()`.

This is the runtime data model behind the actual booking flow.

---

## 14. Event / job / audit flow

The helper file `app/Helpers/helpers.php` defines `auditLog(...)` and `sendNotification(...)`. These functions do not directly insert rows synchronously; they dispatch queue jobs:
- `AuditLogJob::dispatch($data)->afterResponse();`
- `SendNotificationJob::dispatch($data)->afterResponse();`

The jobs are in:
- `app/Jobs/AuditLogJob.php`
- `app/Jobs/SendNotificationJob.php`

Those classes simply create a model row in the queue worker context.

This is the actual event-like integration present in the trace; there is no explicit `BookingCreated` Laravel event class in the booking-to-invoice path I could verify from the relevant files.

---

## 15. Important findings and risk notes

### Key finding 1: invoice is not created automatically at booking creation
The current active code does not create an invoice inside `BookingService::createSingleBooking()`. The invoice creation block is commented out. The actual invoice is created later inside `InvoiceController::store()`.

### Key finding 2: room availability and room assignment are split by design
Availability is checked in `AvailabilityService`, but the actual room number assignment is done later in `RoomAllocationService`. This is a deliberate two-step process and is not a single transaction at the controller layer.

### Key finding 3: booking->guest is not a single guest_id column
The migration history shows that the project evolved away from a direct `bookings.guest_id`; current guest data is stored in `booking_guests` and linked via `booking_id`.

### Key finding 4: the invoice template is primarily a rendering layer
The actual PDF is rendered client-side through `html2canvas` + `jsPDF`. The server delivers a Blade invoice view, and the browser converts it to PDF.

### Key finding 5: there is schema drift and naming inconsistencies
Examples:
- `booking_additional_servives` renamed to `booking_additional_services`
- `bookings.guest_id` originally existed, later removed
- `booking_type` values include `regular`, `bulk`, `hourly` while some runtime logic also uses `booking_type === 'hourly'` and `rate_type` in `Hotel` configuration

These drift points make the project more custom and more fragile than a standard Laravel booking flow.

---

## 16. End-to-end timeline (simplified)

1. User opens booking page and selects create booking.
2. `POST /bookings` goes to `BookingController::store()`.
3. Controller validates and calls `BookingService::createSingleBooking()`.
4. Service validates guest uniqueness, room availability, and calculates price.
5. Booking row is created.
6. Guest rows and additional service rows are created.
7. `RoomAllocationService::allocateRooms()` creates `room_distributions` rows.
8. Payment row is created if payment exists.
9. Booking is set to `confirmed`.
10. User checks in the guest via `POST /booking/check-in`.
11. Controller updates `actual_check_in_at` and booking status to `checked_in`.
12. User checks out via `POST /bookings/{id}/checkout`.
13. Controller updates `actual_check_out_at`, booking status to `checked_out`, and marks room status as `dirty`.
14. User opens invoice generation screen and fetches invoice data.
15. JavaScript builds invoice rows and totals.
16. `POST /invoice-management/generate-invoice` saves invoice and invoice items in `InvoiceController::store()`.
17. `GET /invoice-management/invoices/{invoice}/download` renders the invoice Blade template.
18. Browser-side JS generates PDF from the rendered invoice.

---

## 17. Missing information / unverified items

The following items were not fully discoverable from the traced booking-to-invoice path and should be treated as not explicitly proven by the source code I inspected:
- a dedicated Laravel event or custom observer for booking creation beyond the helper-driven audit log,
- a server-side PDF generation job for the invoice,
- a fully automated invoice generation hook at booking creation,
- deeper business rules for discount policies beyond the code currently present,
- full queue worker setup for `AuditLogJob` and `SendNotificationJob` in the application runtime.

---

## 18. Final conclusion

The actual application workflow is a custom, service-driven booking system with real domain logic in `BookingService`, `AvailabilityService`, `PricingService`, and `RoomAllocationService`, not a simplistic single-step pattern. The booking record is the master reservation, but the actual room occupancy and billing facts are spread across:
- `bookings`,
- `booking_guests`,
- `room_distributions`,
- `booking_additional_services`,
- `booking_payments`,
- `invoices`,
- `invoice_items`.

The invoice is generated only after booking lifecycle events and saved as a separate financial snapshot. The final user-facing invoice is then rendered from Blade and exported to PDF in the browser using `html2canvas` and `jsPDF`.
