    <?php

use App\Http\Controllers\AdditionalServiceController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContactRequestController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\WeeklyOffController;
use App\Http\Controllers\FrontendController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\HotelSetupController;
use App\Http\Controllers\HourlyRateController;
use App\Http\Controllers\InventoryCategoryController;
use App\Http\Controllers\InventoryConsumptionController;
use App\Http\Controllers\InventoryStockAdjustmentController;
use App\Http\Controllers\InventoryGoodsReceiptController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\InventoryPurchaseOrderController;
use App\Http\Controllers\InventoryUnitController;
use App\Http\Controllers\InventoryVendorController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceTemplateController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PriceChartController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoomNumberController;
use App\Http\Controllers\RoomRateController;
use App\Http\Controllers\RoomTypeController;
use App\Http\Controllers\SeasonalRateController;
use App\Http\Controllers\InventoryStockController;
use App\Http\Controllers\InventoryStockLedgerController;
use App\Http\Controllers\ActionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserRoleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeveloperController;

Route::get('/', [FrontendController::class, 'home']);
Route::get('/about-us', [FrontendController::class, 'aboutUs']);
Route::get('/Pricing', [FrontendController::class, 'pricing']);
Route::get('/contact-us', [FrontendController::class, 'contactUs']);
Route::get('/services', [FrontendController::class, 'services']);
Route::get('/login', [FrontendController::class, 'login']);

Route::get('/ho', function () {
    return view('hotel');
});
Route::get('/assign', function () {
    return view('pages.roommanagement.assignrooms.index');
});

Route::post('/contact-request', [ContactRequestController::class, 'store'])->name('contact.request');
Route::match(['get', 'post'], '/register', function () {
    abort(404);
});


// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/toggle-status', [UserController::class, 'toggleStatus']);

    Route::middleware(['role:1'])->group(function () {
        // user roles 
        Route::resource('userroles', UserRoleController::class);
        Route::resource('actions', ActionController::class);

        // contact requests
        Route::resource('contact-requests', ContactRequestController::class, ['only' => ['index', 'show', 'destroy']]);

        // plans 
        Route::resource('plans', PlanController::class);

        // permissions
        Route::resource('permissions', PermissionController::class);

        // admins 
        Route::resource('admins', AdminController::class);
        // Route::get('super-admin-dashboard', [AdminController::class, 'superAdminDashboard'])
        //     ->name('super-admin-dashboard');


        // hotel 
        Route::resource('hotels', HotelController::class);
        Route::post('/hotels/{hotel}/toggle-status', [HotelController::class, 'toggleStatus'])
            ->name('hotels.toggle-status');
        Route::post('/subscriptions/update-dates', [SubscriptionController::class, 'updateDates']);
    });
    // hotel setup 
    Route::get('/hotel/setup', [HotelSetupController::class, 'index'])
        ->name('hotel.setup');
    Route::post('/hotel/setup/basic-info', [HotelSetupController::class, 'saveBasicInfo'])
        ->name('hotel.setup.basic-info');
    Route::post(
        '/hotels/{hotel}/operations',
        [HotelSetupController::class, 'saveOperations']
    )->name('hotel.operations.save');
    Route::post(
        '/hotels/{hotel}/po-settings',
        [HotelSetupController::class, 'savePOSettings']
    )->name('hotel.po-settings.save');
    Route::post(
        '/hotels/{hotel}/pricing',
        [HotelSetupController::class, 'savePricing']
    )->name('hotel.pricing.save');
    Route::post('hotel/{hotel}/tax-billing', [HotelSetupController::class, 'saveTaxBilling'])
        ->name('hotel.setup.tax-billing');
    Route::post('/hotel/{hotel}/booking-settings', [HotelSetupController::class, 'saveBookingSettings'])
        ->name('hotel.setup.booking-settings');

    // Developer 
    Route::resource('developers', DeveloperController::class);


    //users
    Route::post('/users/update-password', [UserController::class, 'updatePassword'])->name('users.updatePassword');
    Route::post('/users/switch-hotel', [UserController::class, 'switch'])
        ->name('users.switchHotel');

    Route::get('/users/stop-impersonation', [UserController::class, 'stop'])
        ->name('users.stopImpersonation');

    // price chart 
    Route::get('/pricechart', [PriceChartController::class, 'index']);
    Route::get('/pricechart/getroomrates', [PriceChartController::class, 'getroomrates']);

    Route::prefix('room-management')->group(function () {
        // roomtype 
        Route::resource('room-types', RoomTypeController::class);
        Route::get('room-types/{id}/guest-config', [RoomTypeController::class, 'guestConfig'])
            ->name('room-types.guest-config');
        // roomnumber 
        Route::resource('room-numbers', RoomNumberController::class);
        // room rates 
        Route::resource('room-rates', RoomRateController::class);
        // seasonal rate 
        Route::resource('seasonal-rates', SeasonalRateController::class);

        Route::resource('hourly-rates', HourlyRateController::class);

    });

    Route::get('/get-states', [CountryController::class, 'getStates'])
        ->name('get.states.by.country');

    // additional_service 
    Route::resource('additional_services', AdditionalServiceController::class);

    // guest 
    Route::resource('guests', GuestController::class);
    Route::get('/guest/find', [GuestController::class, 'find'])
        ->name('guest.find');

    // agent 
    Route::resource('agents', AgentController::class);


    // bookings 
    Route::resource('bookings', BookingController::class);
    Route::get('/availability/search', [AvailabilityController::class, 'search']);
    Route::post('/booking/check-in', [BookingController::class, 'checkIn'])->name('booking.checkin');
    Route::post('/bookings/{id}/checkout', [BookingController::class, 'checkOut'])->name('booking.checkout');
    Route::post('/bookings/{id}/noshow', [BookingController::class, 'noShow'])->name('booking.noshow');
    Route::get('/booking/get-available-rooms/{bookingId}', [BookingController::class, 'getAvailableRooms'])->name('get.available.rooms');
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

    // House Keeping
    Route::get('housekeeping', [BookingController::class, 'housekeeping'])->name('housekeeping.index');
    Route::post('housekeeping/room-status', [BookingController::class, 'updateHousekeepingRoomStatus'])->name('housekeeping.room.status');

    // Room management (changes, replacements, type changes)
    Route::post('/bookings/{bookingId}/change-room', [BookingController::class, 'changeRoom'])->name('booking.changeRoom');
    Route::post('/bookings/{bookingId}/change-room-type', [BookingController::class, 'changeRoomType'])->name('booking.changeRoomType');
    Route::get('/bookings/{bookingId}/replacement-rooms', [BookingController::class, 'getReplacementRooms'])->name('booking.replacementRooms');

    // bookmap
    Route::get('/bookmap', [BookingController::class, 'bookmap']);

    // ticketing system
    Route::resource('tickets', TicketController::class);
    Route::post('/tickets/{ticket}/messages', [TicketController::class, 'storeMessage'])
        ->name('tickets.messages.store');
    Route::get('/tickets/{ticket}/latest-message', [TicketController::class, 'latestMessage']);


    // billing
    Route::prefix('invoice-management')->group(function () {

        Route::get('/generate-invoice', [InvoiceController::class, 'generate']);
        Route::post('/generate-invoice', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
        Route::post('/invoice-templates/select', [InvoiceTemplateController::class, 'select'])
    ->name('invoice-templates.select');
        Route::get('/generate-invoice/{id}/invoice-data', [InvoiceController::class, 'getInvoiceData']);
        Route::resource('templates', InvoiceTemplateController::class);
        Route::post('/templates/{id}/toggle-status', [InvoiceTemplateController::class, 'toggleStatus'])
            ->name('invoice-templates.toggleStatus');

        Route::get('setup', [InvoiceTemplateController::class, 'setup']);
    });

    //audit 
    Route::resource('audit-logs', AuditLogController::class);

    //invoice 
    Route::get('/invoices-{number}', function ($number) {
        return view('invoices.master-invoice1.invoice-' . $number);
    })->name('invoice.template');

    // Inventory Management Routes
    Route::prefix('inventory-management')->group(function () {
        // Category Master
        Route::resource('categories', InventoryCategoryController::class);

        // Unit Master
        Route::resource('units', InventoryUnitController::class);

        // Item Master
        Route::resource('items', InventoryItemController::class);
        Route::get('/items-by-category/{id}', [InventoryItemController::class, 'getItemsByCategory'])
            ->name('items.byCategory');

        //po 
        Route::resource('purchase-orders', InventoryPurchaseOrderController::class);
        Route::post('/purchase-orders/{id}/change-status', [InventoryPurchaseOrderController::class, 'changeStatus'])
            ->name('purchase-orders.changeStatus');
        Route::get(
            '/purchase-orders/{id}/download',
            [InventoryPurchaseOrderController::class, 'download']
        )
            ->name('purchase-orders.download');
        Route::get('/purchase-orders/{id}/items', [InventoryPurchaseOrderController::class, 'getItems']);

        // Vendor Master
        Route::resource('vendors', InventoryVendorController::class);

        //grn
        Route::resource('grn', InventoryGoodsReceiptController::class);
        Route::get('/grnitems/{id}/items', [InventoryGoodsReceiptController::class, 'getItems']);
        Route::get('/vendors/{vendor}/purchase-orders', [InventoryGoodsReceiptController::class, 'getPOByVendor']);

        //stock
        Route::get('/stock-overview', [InventoryStockController::class, 'index'])
            ->name('stock.index');

        //stockledger
        Route::get('/stock-ledger', [InventoryStockLedgerController::class, 'index'])
            ->name('stock.ledger');
        Route::get('/stock-ledger/{id}', [InventoryStockLedgerController::class, 'show']);

        //consumption
        Route::resource('consumption', InventoryConsumptionController::class);

        //adjustments
        Route::resource('stock-adjustments', InventoryStockAdjustmentController::class);

        //stock-transactions
        Route::get('stock-transactions', [InventoryStockController::class, 'stockTransactions'])->name('stock.transactions');
        Route::get('/stock-transactions/{id}', [InventoryStockController::class, 'show'])->name('stock-transactions.show');
    });

    Route::prefix('employee-management')->group(function () {
        Route::resource('employees', EmployeeController::class);
        Route::post('/employees/{employee}/weekly-offs', [WeeklyOffController::class, 'store'])
            ->name('employees.weekly-offs.store');
        Route::resource('leaves', LeaveController::class);
        Route::post('/leaves/{id}/approve', [LeaveController::class, 'approve'])->name('leaves.approve');
        Route::post('/leaves/{id}/reject', [LeaveController::class, 'reject'])->name('leaves.reject');

        //department
        Route::resource('departments', DepartmentController::class);
        Route::post('/departments/{id}/toggle-status', [DepartmentController::class, 'toggleStatus'])
            ->name('departments.toggleStatus');

        //holidays
        Route::resource('holidays', HolidayController::class);
        Route::post('/holidays/{id}/toggle-status', [HolidayController::class, 'toggleStatus'])
            ->name('holidays.toggleStatus');

        Route::resource('roles', RoleController::class);

        Route::resource('shifts', ShiftController::class);
        Route::post('/shifts/{id}/toggle-status', [ShiftController::class, 'toggleStatus'])
            ->name('shifts.toggleStatus');
        Route::resource('attendances', AttendanceController::class);
        Route::get('check-attendances', [AttendanceController::class, 'checkAttendances'])->name('check-attendances');


        // Route::resource('leaves', LeaveController::class);
        // Route::resource('payrolls', PayrollController::class);
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Notifications
    Route::post('/notifications/read/{id}', [NotificationController::class, 'markAsRead']);
});

Route::get('/dummy', function () {
    return view('dummy');
});
Route::get('/hotel', function () {
    return view('hotel');
});




require __DIR__ . '/auth.php';
