<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserEmployeeSyncController;
use App\Http\Controllers\SalesEmployeeFlatController;

use App\Http\Controllers\Api\{
    AccountClassController,
    AccountDashboardController,
    AccountGroupController,
    AccountLedgerNameController,
    AccountSubGroupController,
    AllViewController,
    BankListController,
    CategoryController,
    ChildCategoryController,
    CompanyController,
    DailyPriceController,
    DailyPriceHistoryController,
    DepartmentController,
    DesignationController,
    EmployeeController,
    LocationController,
    RegisterController,
    SectorController,
    UserController,
    UserActivityController,
    LogActivityController,
    QuantityTypeController,
    DealerController,
    DiscountLedgerController,
    FarmEggStockController,
    PaymentTypeController,
    ProductController,
    SalesAddressMapController,
    SalesBookingController,
    SalesDraftController,
    SalesHasPaymentsController,
    SalesOrderController,
    SpEggStockController,
    SubCategoryController,
    UnitController,
    ZoneController,
    StockAdjustmentController,
    AuditLogController,
    BankLedgerController,
    BankTransferInfoController,
    CompanyLedgerController,
    EggTransferController,
    EpLedgerController,
    FarmEggProductionController,
    NotificationController,
    LedgerController,
    InventoryController,
    SalesLogController,
    TransportLogController,
    ProductTypeController,
    SalesEndpointController,
    // Added new controllers
    ReturnLogController,
    SalesReturnController,
    TransportOrderController,
    EggReceiveController,
    EggStockAdjustmentController,
    EggStockController,
    FlockController,
    JournalEntryController,
    LedgerReportController,
    PaymentReceiveInfoController,
    SendSmsController,
    JournalEntry\DebitVoucherController,
    PaymentPayableController,
    CommissionController,
    DealerViewController,
    EmpManageGroupController,
    EmpSalesGroupController,
    SmsController,
    PaymentReceiveReportController,
    WeightTransactionController,
    WCustomerController,
    WMaterialController,
    WVendorController

};
use App\Http\Controllers\Api\ImageUploadController;
use App\Http\Controllers\Api\Chicks\BreedController;
use App\Http\Controllers\Api\Chicks\ChicksBookingController;
use App\Http\Controllers\Api\Chicks\ChicksDailyPriceController;
use App\Http\Controllers\Api\Chicks\ChicksDailyPriceHistoryController;
use App\Http\Controllers\Api\Chicks\ChicksFarmProductionController;
use App\Http\Controllers\Api\Chicks\ChicksPriceController;
use App\Http\Controllers\Api\Chicks\ChicksProductionLedgerController;
use App\Http\Controllers\Api\Chicks\ChicksStockAdjustmentController;
use App\Http\Controllers\Api\Chicks\ChicksStockController;
use App\Http\Controllers\Api\Feed\FeedBookingController;
use App\Http\Controllers\Api\Feed\FeedDeliveryController;
use App\Http\Controllers\Api\Feed\FeedDraftController;
use App\Http\Controllers\Api\Feed\FeedFarmProductionController;
use App\Http\Controllers\Api\Feed\FeedOrderController;
use App\Http\Controllers\Api\Feed\FeedProductionLedgerController;
use App\Http\Controllers\Api\Feed\FeedReceiveController;
use App\Http\Controllers\Api\Feed\FeedSalesReturnController;
use App\Http\Controllers\Api\Feed\FeedStockAdjustmentController;
use App\Http\Controllers\Api\Feed\FeedStockController;
use App\Http\Controllers\Api\Feed\FeedTransferController;
use App\Http\Controllers\Api\Feed\LabourInfoController;
use App\Http\Controllers\Api\Feed\LabourDetailController;
use App\Http\Controllers\Api\Feed\LabourPaymentController;
use App\Http\Controllers\Api\Feed\FeedReportController;
use App\Http\Controllers\Api\Feed\FeedSalesSummeryController;
use App\Http\Controllers\Api\OrderDeliveryController;
use App\Services\SalesEmployeeSyncService;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
    /////////////////////////////////////Weight Scale Route //////////////
// 📝 Create (store)
Route::post('/storeweight-transactions', [WeightTransactionController::class, 'store']);

// Image upload from camera (one POST per camera)
Route::post('/v1/images/upload', [ImageUploadController::class, 'upload']);

// 📋 Read all (index)
Route::get('/getweight-transactions', [WeightTransactionController::class, 'index']);

// 🔍 Read one (show)
Route::get('/weight-transactions/{id}', [WeightTransactionController::class, 'show']);

// ✏️ Update one
Route::put('/updateweight-transactions/{id}', [WeightTransactionController::class, 'update']);

// 🗑️ Delete one
Route::delete('/weight-transactions/{id}', [WeightTransactionController::class, 'destroy']);
Route::apiResource('w-customer',WCustomerController::class);
Route::apiResource('w-material',WMaterialController::class);
Route::apiResource('w-vendor',WVendorController::class);
Route::get('/dealer-view-all', [AllViewController::class, 'dealerAllView']);
Route::get('/sector-view-all', [AllViewController::class, 'sectorAllView']);
Route::get('/productType', [AllViewController::class, 'productType']);



///////////////////////////////////





Route::group(['prefix' => 'v2'], function () {
    Route::post('register', [UserController::class, 'storeAdminUser']);
    Route::post('/a/login', [RegisterController::class, 'login']);
    Route::get('/send-sms', [SendSmsController::class, 'sendSmsNotification']);
    Route::get('/check-sms-balance', [SendSmsController::class, 'checkBalance']);
});

Route::group(['prefix' => 'v2', 'middleware' => 'jwt.verify'], function () {

    Route::post('/sms/send', [SmsController::class, 'sendToClient']);
    Route::apiResource('sms',SmsController::class);

Route::get('/sync-sales-employees', function (SalesEmployeeSyncService $syncService) {
        $result = $syncService->sync();

        return response()->json([
            'success' => $result['status'],
            'new_records_added' => $result['new'],
            'message' => $result['status']
                ? "{$result['new']}টি নতুন রেকর্ড সংযুক্ত হয়েছে।"
                : "ডেটা সিঙ্ক ব্যর্থ হয়েছে।"
        ]);
    });

    Route::get('/sales-employees-flat', [SalesEmployeeFlatController::class, 'index']);
    Route::post('/sales-employees-flat', [SalesEmployeeFlatController::class, 'store']);



    // Location Management
    Route::get('get-countries', [LocationController::class, 'getAllCountries'])->name('get-countries');
    Route::get('get-divisions', [LocationController::class, 'getAllDivisions'])->name('get-divisions');
    Route::get('get-only-divisions', [LocationController::class, 'getOnlyDivisions'])->name('get-only-divisions');
    Route::get('get-districts', [LocationController::class, 'getDistricts'])->name('getDistrict');
    Route::get('get-districts/{division_id}', [LocationController::class, 'getAllDistricts'])->name('get-districts');
    Route::get('get-upazilas/{district_id}', [LocationController::class, 'getAllUpazilas'])->name('get-upazilas');
    Route::get('get-upazilas', [LocationController::class, 'getUpazilas'])->name('getUpazila');

    // User Management
    Route::get('get/data', [RegisterController::class, 'data']);
    Route::get('logout', [RegisterController::class, 'logout']);
    Route::get('/user/list', [UserController::class, 'allUser']);
    Route::get('/get-my-info', [UserController::class, 'getSelf']);
    Route::get('/user-single/{id}', [UserController::class, 'getAdminUser']);
    Route::post('/user/store', [UserController::class, 'storeAdminUser']);
    Route::get('/get-my-information/{userId}', [UserController::class, 'getAdminUser']);
    Route::put('/user/update/{userId}', [UserController::class, 'updateAdminUser']);
    Route::delete('/user/delete/{userId}', [UserController::class, 'deleteUser']);
    Route::post('/users/status/update/{id}', [UserController::class, 'statusUpdate'])->name('Status-update');
    Route::get('/user/listByValue', [UserController::class, 'userFilter']);

    // Admin Roles and Permissions
    Route::get('/get_roles', [\App\Http\Controllers\Api\RoleController::class, 'index']);
    Route::post('/add/role', [\App\Http\Controllers\Api\RoleController::class, 'storeRole']);
    Route::get('/single_role/{id}', [\App\Http\Controllers\Api\RoleController::class, 'search_by_role']);
    Route::post('/update_role/{id}', [\App\Http\Controllers\Api\RoleController::class, 'update_role_By_RoleId']);
    Route::delete('/delete_role/{id}', [\App\Http\Controllers\Api\RoleController::class, 'delete_role_By_RoleId']);
    Route::get('/get_permissions', [\App\Http\Controllers\Api\RoleController::class, 'permission']);
    Route::post('/store/permission', [\App\Http\Controllers\Api\RoleController::class, 'store']);
    Route::post('/update_permission/{id}', [\App\Http\Controllers\Api\RoleController::class, 'updatePermission']);
    Route::delete('/delete_permission/{id}', [\App\Http\Controllers\Api\RoleController::class, 'deletePermission']);
    Route::get('/role/permission/list', [\App\Http\Controllers\Api\RoleController::class, 'getRolePermission']);
    Route::post('/role/permission/store', [\App\Http\Controllers\Api\RoleController::class, 'storeRolePermission']);
    Route::post('/role/permission/update', [\App\Http\Controllers\Api\RoleController::class, 'updateRolePermission']);
    Route::get('/getPermissionByRole', [\App\Http\Controllers\Api\RoleController::class, 'getPermissionByRole']);

    // Sector, Department, Designation Management
    Route::apiResource('sectors', SectorController::class);
    Route::get('/getsectorlisturl', [SectorController::class, 'getSectorList'])->name('getSectorList');
    Route::get('/getSectorFilterList', [SectorController::class, 'getSectorFilterList'])->name('getSectorFilterList');
    Route::get('/get-sales-point', [SectorController::class, 'getSalesPointList'])->name('getSalesPointList');
    Route::get('/get-farm-list', [SectorController::class, 'getFarmList'])->name('getFarmList');
    Route::get('/get-sales-point-filter', [SectorController::class, 'getSalesPointFilterList'])->name('getSalesPointFilterList');
    Route::get('/get-farm-filter', [SectorController::class, 'getFarmFilterList'])->name('getFarmFilterList');
    Route::get('/get-sec-type-filter', [SectorController::class, 'getSectorTypeFilterList'])->name('getSectorTypeFilterList');

    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('designations', DesignationController::class);

    // Company Management
    Route::apiResource('companies', CompanyController::class); // Manage companies

    // BankList, PaymentType Management
    Route::apiResource('bank-lists', BankListController::class);
    Route::get('/get-bank-list', [BankListController::class, 'getBankList'])->name('getBankList');
    Route::get('/bankListForAccounts', [AccountLedgerNameController::class, 'getBankDropdown']);
    Route::get('/dealerListForAccounts', [AccountLedgerNameController::class, 'getDealerDropdown']);

    Route::apiResource('p-types', PaymentTypeController::class);

    // Zone Management
    Route::apiResource('zones', ZoneController::class);

    // DailyPrice, DailyPriceHistory Management
    Route::apiResource('d-prices', DailyPriceController::class);
    Route::apiResource('daily-p-h', DailyPriceHistoryController::class);

    // SalesOrder, SalesDraft, SalesBooking, SalesReturn, DiscountLedger Management
    Route::apiResource('sales-orders', SalesOrderController::class);
    Route::get('/get-order-list', [SalesOrderController::class, 'getOrderList'])->name('getOrderList');
    Route::get('/get-re-order-list', [SalesOrderController::class, 'getReOrderList'])->name('getReOrderList');
    Route::get('/get-sales-data', [SalesOrderController::class, 'getSalesOrdersData']);
    Route::post('/get-product-price', [SalesOrderController::class, 'getProductDailyPrice']);

    //Order delivery system routes
    Route::post('/order-delivery/store', [OrderDeliveryController::class, 'store'])->name('order-delivery.store');
    Route::get('/order-delivery/list', [OrderDeliveryController::class, 'index'])->name('order-delivery.index');
    Route::get('/order-delivery/{id}', [OrderDeliveryController::class, 'show'])->name('order-delivery.show');
    Route::put('/order-delivery/status/update/{id}', [OrderDeliveryController::class, 'updateStatus'])->name('order-delivery.status.update');
    Route::delete('/order-delivery/delete/{id}', [OrderDeliveryController::class, 'destroy'])->name('order-delivery.delete');

    Route::apiResource('sales-drafts', SalesDraftController::class);
    Route::apiResource('s-bookings', SalesBookingController::class);
    Route::apiResource('s-returns', SalesReturnController::class);
    Route::get('/get-book-list', [SalesBookingController::class, 'getBookList'])->name('getBookList');
    Route::apiResource('dis-ledgers', DiscountLedgerController::class);

    // FarmEggProduction, EpLedger, EggTransfer Management
    Route::apiResource('ep-ledgers', EpLedgerController::class);
    // Product Type Management
    Route::apiResource('product-types', ProductTypeController::class); // Manage product types

    // Product Management
    Route::apiResource('products', ProductController::class); // Manage products
    Route::get('/get-pro-list', [ProductController::class, 'getProList'])->name('getProList');
    Route::get('/get-cate-pro-list', [ProductController::class, 'getProCateList'])->name('getProCateList');
    Route::get('products/child-category/{id}', [ProductController::class, 'getProductsByChildCategory']);
    Route::get('pro/c-category/{id}', [ProductController::class, 'getProductStockByFilters']);
    Route::get('/getChildCateProList', [ProductController::class, 'getChildCateProductApproveList'])->name('getChildCateProductApproveList');
    //feed
    Route::get('pro/fc-category/{id}', [ProductController::class, 'getFeedProductStockByFilters']);
    Route::get('feed-products/child-category/{id}', [ProductController::class, 'getFeedProductsByChildCategory']);
    Route::get('/approved-feed-products', [ProductController::class, 'getApprovedFeedProducts']);
    Route::get('/approved-chicks-products', [ProductController::class, 'getApproveChicksProducts']);

    // Category Management
    Route::apiResource('categories', CategoryController::class); // Manage categories
    Route::get('/get-cate-list', [CategoryController::class, 'getCateList'])->name('getCateList');

    // SubCategory Management
    Route::apiResource('sub-categories', SubCategoryController::class); // Manage subcategories
    Route::get('/get-sub-active-list', [SubCategoryController::class, 'getSubActiveList'])->name('getSubActiveList');

    // ChildCategory Management
    Route::apiResource('child-categories', ChildCategoryController::class); // Manage child categories
    Route::get('/get-child-cate-list', [ChildCategoryController::class, 'getChildCateList'])->name('getChildCateList');
    Route::get('/get-child-active-list', [ChildCategoryController::class, 'getChildActiveList'])->name('getChildActiveList');
    Route::get('/get-feed-child-cate-list', [ChildCategoryController::class, 'getFeedChildCateList'])->name('getFeedChildCateList');
    Route::get('/get-chicks-child-cate-list', [ChildCategoryController::class, 'getChicksChildCateList'])->name('getChicksChildCateList');

    // Unit, QuantityType Management
    Route::apiResource('units', UnitController::class); // Manage production units
    Route::apiResource('q-types', QuantityTypeController::class);
    // Sales Endpoint Management
    Route::apiResource('sales-endpoints', SalesEndpointController::class); // Manage sales endpoints

    // Inventory Management
    Route::apiResource('inventories', InventoryController::class); // Manage inventories
    Route::get('/inventories/current-stock', [InventoryController::class, 'getCurrentStock'])->name('getCurrentStock');
    Route::get('/inventories/category-wise-stock', [InventoryController::class, 'getCategoryWiseStock'])->name('getCategoryWiseStock');
    Route::get('/inventories/{unit_id}/track-supply', [InventoryController::class, 'trackSupplyByUnit'])->name('trackSupplyByUnit');

    // Transport Management
    Route::apiResource('transport-logs', TransportLogController::class); // Manage transport logs
    Route::get('/transport-logs/track-transport/{transport_id}', [TransportLogController::class, 'trackTransport'])->name('trackTransport');

    // Transport Order Management
    Route::apiResource('transport-orders', TransportOrderController::class); // Manage transport orders
    Route::post('/transport-orders/{id}/confirm-delivery', [TransportOrderController::class, 'confirmDelivery'])->name('transportOrders.confirmDelivery');

    // Sales and Market Authority Management
    Route::apiResource('sales-logs', SalesLogController::class); // Manage sales logs
    Route::get('/sales-logs/by-market/{market_id}', [SalesLogController::class, 'getSalesLogsByMarket'])->name('getSalesLogsByMarket');
    Route::get('/sales-logs/unsold-items', [SalesLogController::class, 'getUnsoldItems'])->name('getUnsoldItems');

    // Return Management
    Route::apiResource('return-logs', ReturnLogController::class); // Manage return logs

    // Ledger and Journal Management
    Route::apiResource('ledgers', LedgerController::class); // Manage ledgers
    Route::get('/ledgers/journal', [LedgerController::class, 'getJournal'])->name('getJournal');
    Route::get('/ledgers/unit-journal/{unit_id}', [LedgerController::class, 'getUnitJournal'])->name('getUnitJournal');
    Route::get('/ledgers/transport-journal/{transport_id}', [LedgerController::class, 'getTransportJournal'])->name('getTransportJournal');

    // Stock Adjustment Management
    Route::apiResource('stock-adjustments', StockAdjustmentController::class); // Manage stock adjustments
    Route::get('/stock-adjustments/inventory/{inventory_id}', [StockAdjustmentController::class, 'getStockAdjustmentsByInventory'])->name('getStockAdjustmentsByInventory');

    // Audit Logs
    Route::apiResource('audit-logs', AuditLogController::class); // View audit logs
    Route::get('/audit-logs/{model_type}/{model_id}', [AuditLogController::class, 'getAuditLogsByModel'])->name('getAuditLogsByModel');

    // Notifications
    Route::apiResource('notifications', NotificationController::class); // Manage notifications
    Route::put('/notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('markNotificationAsRead');
    Route::get('/notifications/unread', [NotificationController::class, 'getUnreadNotifications'])->name('getUnreadNotifications');

    // Status Updates for Different Modules
    Route::post('/sec/status/update/{id}', [SectorController::class, 'statusUpdate'])->name('Status-update');
    Route::post('/dep/status/update/{id}', [DepartmentController::class, 'statusUpdate'])->name('Status-update');
    Route::post('/com/status/update/{id}', [CompanyController::class, 'statusUpdate'])->name('Status-update');
    Route::post('/deg/status/update/{id}', [DesignationController::class, 'statusUpdate'])->name('Status-update');
    Route::post('/emp/status/update/{id}', [EmployeeController::class, 'statusUpdate'])->name('Status-update');
    Route::post('/del/status/update/{id}', [DealerController::class, 'statusUpdate'])->name('Status-update');
    Route::post('/pro/status/update/{id}', [ProductController::class, 'statusUpdate'])->name('Status-update');
    Route::post('/cat/status/update/{id}', [CategoryController::class, 'statusUpdate'])->name('Status-update');
    Route::post('/sub/status/update/{id}', [SubCategoryController::class, 'statusUpdate'])->name('Status-update');
    Route::post('/chi/status/update/{id}', [ChildCategoryController::class, 'statusUpdate'])->name('Status-update');
    Route::post('/uni/status/update/{id}', [UnitController::class, 'statusUpdate'])->name('Status-update');
    Route::post('/bnk/status/update/{id}', [BankListController::class, 'statusUpdate'])->name('Status-update');
    Route::post('/dpr/status/update/{id}', [DailyPriceController::class, 'statusUpdate'])->name('Status-update');
    Route::post('/sor/status/update/{id}', [SalesOrderController::class, 'statusUpdate'])->name('Status-update');
    Route::post('/draft/status/update/{id}', [SalesDraftController::class, 'statusUpdate'])->name('Status-update');
    Route::post('/sbo/status/update/{id}', [SalesBookingController::class, 'statusUpdate'])->name('Status-update');
    Route::post('/sr/status/update/{id}', [SalesReturnController::class, 'statusUpdate'])->name('Status-update');
    Route::post('/fsg/status/update/{id}', [FarmEggStockController::class, 'statusUpdate'])->name('Status-update');
    Route::post('/fep/status/update/{id}', [FarmEggProductionController::class, 'statusUpdate'])->name('Status-update');
    Route::post('/epl/status/update/{id}', [EpLedgerController::class, 'statusUpdate'])->name('Status-update');
    Route::post('/bti/status/update/{id}', [BankTransferInfoController::class, 'statusUpdate'])->name('Status-update');
    Route::post('/flc/status/update/{id}', [FlockController::class, 'statusUpdate'])->name('Status-update');
    Route::post('/esg/status/update/{id}', [EmpSalesGroupController::class, 'statusUpdate'])->name('Status-update');

    // UserActivity & LogActivity
    Route::get('/get/list', [UserActivityController::class, 'index'])->name('get_list');
    Route::get('/get/useracti_list', [UserActivityController::class, 'useracti_list'])->name('useracti_list');
    Route::get('/export/useractivity/csv', [UserActivityController::class, 'exportExcel']);
    Route::delete('/delete/user/list', [UserActivityController::class, 'destroy'])->name('delete_activity_list');
    Route::get('/get/log/list', [LogActivityController::class, 'index'])->name('get-list');
    Route::delete('/delete/log/list', [LogActivityController::class, 'destroy'])->name('delete_list');

    Route::apiResource('farm-egg-productions', FarmEggProductionController::class);
    Route::prefix('farm-egg-productions')->group(function () {
        Route::post('/multiStatus', [FarmEggProductionController::class, 'updateMultiStatus']); // Update multiple statuses
        Route::get('/', [FarmEggProductionController::class, 'index']); // Fetch all records
        Route::get('/{id}', [FarmEggProductionController::class, 'show']); // Fetch single record
        Route::post('/', [FarmEggProductionController::class, 'store']); // Create a new record
        Route::post('/{id}/status', [FarmEggProductionController::class, 'updateStatus']); // Update status
        Route::delete('/{id}', [FarmEggProductionController::class, 'destroy']); // Delete a record
        Route::post('/{id}', [FarmEggProductionController::class, 'update']); // Update a record
    });
    Route::get('farmEggStock-balance', [FarmEggProductionController::class, 'getTotalStockAndClosingBalance']);


    Route::apiResource('egg-transfers', EggTransferController::class);

    Route::prefix('egg-transfers')->group(function () {
        Route::get('/', [EggTransferController::class, 'index']); // List all egg transfers
        Route::post('/', [EggTransferController::class, 'store']); // Create new egg transfer
        Route::get('/{id}', [EggTransferController::class, 'show']); // Show single egg transfer details
        Route::post('/{id}', [EggTransferController::class, 'update']); // Update egg transfer
        Route::post('/{id}/status', [EggTransferController::class, 'updateStatus']); // Update status of egg transfer
        Route::post('/{id}/decline', [EggTransferController::class, 'declineTransfer']);

    });
    Route::get('/get-tr-list', [EggTransferController::class, 'getTransferList'])->name('getTransferList');
    Route::get('/get-tr-sec-list', [EggTransferController::class, 'getTransferSecList'])->name('getTransferSecList');

    Route::apiResource('egg-receives', EggReceiveController::class);
    Route::prefix('egg-receives')->group(function () {
        Route::get('/', [EggReceiveController::class, 'index']); // List all egg receives
        Route::post('/', [EggReceiveController::class, 'store']); // Create new egg receive
        Route::get('/{id}', [EggReceiveController::class, 'show']); // Show single egg receive details
        Route::post('/{id}', [EggReceiveController::class, 'update']); // Update egg receive
        Route::post('/{id}/status', [EggReceiveController::class, 'updateStatus']); // Update status of egg receive
        Route::delete('/{id}', [EggReceiveController::class, 'destroy']); // Delete egg receive
    });

    // StockAdjustment Management
    Route::apiResource('egg-adjustments', EggStockAdjustmentController::class);
    Route::prefix('egg-adjustments')->group(function () {
        Route::get('/', [EggStockAdjustmentController::class, 'index']); // List all egg adjustments
        Route::get('/details/{id}', [EggStockAdjustmentController::class, 'getAdjustmentDetails']); // List all egg adjustments
        Route::post('/', [EggStockAdjustmentController::class, 'store']); // Create new egg adjustments
        Route::get('/{adjId}', [EggStockAdjustmentController::class, 'show']); // Show single egg adjustments details
        //Route::post('/{id}', [EggStockAdjustmentController::class, 'update']); // Update egg adjustments
        Route::post('/{adjId}', [EggStockAdjustmentController::class, 'update']);
        Route::post('/{id}/status', [EggStockAdjustmentController::class, 'updateStatus']); // Update status of egg adjustments

        Route::delete('/{id}', [EggStockAdjustmentController::class, 'destroy']); // Delete egg adjustments
    });

    // Flock, EggStock Management
    Route::apiResource('egg-stocks', EggStockController::class);
    Route::get('/get-EggStock', [EggStockController::class, 'getEggStock']);
    Route::get('/get-production-EggStock', [EggStockController::class, 'getProductStocksByChildCategory']);

    Route::apiResource('flocks', FlockController::class);
    // CompanyLedger Management
    Route::apiResource('com-ledgers', CompanyLedgerController::class);

    // PaymentReceiveInfo Management
    Route::apiResource('pr-infos', PaymentReceiveInfoController::class);
    Route::get('/auto-gen-next-vou-no', [PaymentReceiveInfoController::class, 'autoGenerateNextVouNo']);
    Route::post('/update-payment-receive-status/{id}', [PaymentReceiveInfoController::class, 'updateStatus']);
    Route::post('/multiStatus', [PaymentReceiveInfoController::class, 'updateMultiStatus']);

    // BankLedger, BankTransferInfo Management
    Route::apiResource('bank-ledgers', BankLedgerController::class);
    Route::apiResource('bt-infos', BankTransferInfoController::class);
    Route::get('/auto-gen-next-btr-id', [BankTransferInfoController::class, 'autoGenerateNextBtrId']);

    // Account Class, Group, SubGroup, ledgerName Management
    Route::apiResource('acc-class', AccountClassController::class);
    Route::apiResource('acc-group', AccountGroupController::class);
    Route::get('/groups-subgroups', [AccountClassController::class, 'getGroupsAndSubGroupsBy']);
    Route::post('/get-auto-code', [AccountLedgerNameController::class, 'getAutoCode']);

    Route::apiResource('acc-sub-group', AccountSubGroupController::class);
    Route::apiResource('acc-ledger-name', AccountLedgerNameController::class);
    Route::get('/ledger-names/d', [AccountLedgerNameController::class, 'getDLedgerNames']);
    Route::get('/ledger-names/fd', [AccountLedgerNameController::class, 'getFeedDLedgerNames']);
    Route::get('/ledger-names/ed', [AccountLedgerNameController::class, 'getEggDLedgerNames']);

    // Dealer Management
    Route::apiResource('dealers', DealerController::class);
    Route::get('/get-deal-list', [DealerController::class, 'getDealList'])->name('getDealList');
    Route::post('/dealersstore', [DealerController::class, 'store']);
    Route::get('/get-egg-deal-list', [DealerController::class, 'getEggDealList'])->name('getEggDealList');
    Route::get('/get-feed-deal-list', [DealerController::class, 'getFeedDealList'])->name('getFeedDealList');

    // cash clear
    Route::get('clear', [UserController::class, 'clear']);

    //sales revenue heads company wise
    Route::post('/get-sales-revenue-heads', [AccountLedgerNameController::class, 'getSalesRevenueHeads']);

    //Dealer CurrentBalance
    Route::get('/get-dealer-current-balance/{id}', [DealerController::class, 'getDealerBalance']);
    Route::post('/company-wise-dealer-ledger-report/{id}', [LedgerReportController::class, 'companyWiseDealerLedgerReport']);
    Route::post('/dealer-ledger-report/{id}', [LedgerReportController::class, 'dealerLedgerReport']);
    Route::post('/bank-ledger-report/{id}', [LedgerReportController::class, 'bankLedgerReport']);
    Route::post('/dealer-closing-balance-report', [LedgerReportController::class, 'getDealerClosingBalanceReport']);
  //
   Route::post('/chart-of-head-ledger-report/{id}', [LedgerReportController::class, 'chartOfHeadLedgerReport']);

    // DealerGroup wise Egg,Feed ClosingBalance
    Route::get('/getFeedDealerClosingBalance', [LedgerReportController::class, 'getFeedDealerClosingBalanceReport']);
    Route::get('/getEggDealerClosingBalance', [LedgerReportController::class, 'getEggDealerClosingBalanceReport']);
    //Payment Receive Info Update
    Route::post('/payment-receive-info-update/{id}', [PaymentReceiveInfoController::class, 'update']);

    //Journal Entry Debit Voucher
    Route::prefix('journal')->group(function () {
        Route::get('/debit-vouchers', [DebitVoucherController::class, 'index']);
        Route::get('/debit-vouchers/{id}', [DebitVoucherController::class, 'show']);
        Route::post('/debit-vouchers/store', [DebitVoucherController::class, 'store']);
        Route::post('/debit-vouchers/{id}/update', [DebitVoucherController::class, 'update']);
        Route::delete('/debit-vouchers/{id}', [DebitVoucherController::class, 'destroy']);
        Route::post('/debit-vouchers/{id}/status', [DebitVoucherController::class, 'updateStatus']);
    });


    //Payment Payable Info Update
    Route::apiResource('pp-infos', PaymentPayableController::class);
    Route::post('/pp-infos/{id}/update', [PaymentPayableController::class, 'update']);
    Route::post('/pp-infos/{id}/update-status', [PaymentPayableController::class, 'updateStatus']);
    Route::delete('/pp-infos/{id}/delete', [PaymentPayableController::class, 'delete']);
    Route::get('/auto-gen-pay-vou-no', [PaymentPayableController::class, 'autoGeneratePayVouNo']);

    // JournalEntry Management
    Route::apiResource('journal-entries', JournalEntryController::class);

    Route::post('/journal-entries/{id}/update', [JournalEntryController::class, 'update']);

    Route::post('/journal-entries/update-status', [JournalEntryController::class, 'updateStatus']);
    Route::get('/auto-gen-jre-vou-no', [JournalEntryController::class, 'autoGenerateJournalVouNo']);

    // Feed Section
    // Feed Farm Production Management
    Route::apiResource('feed-farm-productions', FeedFarmProductionController::class);
    Route::prefix('feed-farm-productions')->group(function () {
        Route::post('/multiStatus', [FeedFarmProductionController::class, 'updateMultiStatus']);
        Route::get('/', [FeedFarmProductionController::class, 'index']);
        Route::get('/{id}', [FeedFarmProductionController::class, 'show']);
        Route::post('/', [FeedFarmProductionController::class, 'store']);
        Route::post('/{id}/status', [FeedFarmProductionController::class, 'updateStatus']);
        Route::delete('/{id}', [FeedFarmProductionController::class, 'destroy']);
        Route::post('/{id}', [FeedFarmProductionController::class, 'update']);
    });
    Route::get('farmFeedStock-balance', [FeedFarmProductionController::class, 'getFeedTotalStockAndClosingBalance']);

    //  Feed Stock Management
    Route::apiResource('feed-stocks', controller: FeedStockController::class);
    Route::get('/get-feedStock', [FeedStockController::class, 'getFeedStock']);
    Route::get('/get-production-FeedStock', [FeedStockController::class, 'getFeedProductStocksByChildCategory']);

    // Feed StockAdjustment Management
    Route::apiResource('feed-adjustments', FeedStockAdjustmentController::class);
    Route::prefix('feed-adjustments')->group(function () {
        Route::get('/', [FeedStockAdjustmentController::class, 'index']);
        Route::get('/details/{id}', [FeedStockAdjustmentController::class, 'getFeedAdjustmentDetails']);
        Route::post('/', [FeedStockAdjustmentController::class, 'store']);
        Route::get('/{adjId}', [FeedStockAdjustmentController::class, 'show']);
        Route::post('/{adjId}', [FeedStockAdjustmentController::class, 'update']);
        Route::post('/{id}/status', [FeedStockAdjustmentController::class, 'updateStatus']);
        Route::delete('/{id}', [FeedStockAdjustmentController::class, 'destroy']);
    });

    // Feed Production Ledger Management
    Route::apiResource('fp-ledgers', FeedProductionLedgerController::class);
    Route::post('/fpl/status/update/{id}', [FeedProductionLedgerController::class, 'statusUpdate'])->name('Status-update');


    // Feed Transfer Management
    Route::apiResource('feed-transfers', FeedTransferController::class);
    Route::prefix('feed-transfers')->group(function () {
        Route::get('/', [FeedTransferController::class, 'index']);
        Route::post('/', [FeedTransferController::class, 'store']);
        Route::get('/{id}', [FeedTransferController::class, 'show']);
        Route::post('/{id}', [FeedTransferController::class, 'update']);
        Route::post('/{id}/status', [FeedTransferController::class, 'updateStatus']);
        Route::post('/{id}/decline', [FeedTransferController::class, 'declineFeedTransfer']);
    });
    Route::get('/get-feed-tr-list', [FeedTransferController::class, 'getTransferList'])->name('getFeedTransferList');
    Route::get('/get-feed-tr-sec-list', [FeedTransferController::class, 'getFeedTransferSecList'])->name('getTransferSecList');

    // Feed Receive Management
    Route::apiResource('feed-receives', FeedReceiveController::class);
    Route::prefix('feed-receives')->group(function () {
        Route::get('/', [FeedReceiveController::class, 'index']);
        Route::post('/', [FeedReceiveController::class, 'store']);
        Route::get('/{id}', [FeedReceiveController::class, 'show']);
        Route::post('/{id}', [FeedReceiveController::class, 'update']);
        Route::post('/{id}/status', [FeedReceiveController::class, 'updateStatus']);
        Route::delete('/{id}', [FeedReceiveController::class, 'destroy']);
    });

    // Feed Commission Management

    Route::apiResource('commission', CommissionController::class);
    Route::post('/commission/status/update/{id}', [CommissionController::class, 'changeStatus'])->name('Status-update');
    Route::get('/get-commis-list', action: [CommissionController::class, 'getCommissionList'])->name('getCommissionList');
    Route::get('/dealer-commissions/{dealerId}', [CommissionController::class, 'getDealerCommissions']);
    Route::get('/dealer-commissions-amount/{dealerId}', [CommissionController::class, 'getDealerCommissionsAmount']);


    // Feed Sales Return
    Route::apiResource('fs-returns', FeedSalesReturnController::class);
    Route::post('/fsr/status/update/{id}', [FeedSalesReturnController::class, 'statusUpdate'])->name('Status-update');

    //Feed Booking
    Route::apiResource('f-bookings', FeedBookingController::class);
    Route::get('/get-feed-book-list', [FeedBookingController::class, 'getFeedBookList'])->name('getFeedBookList');
    Route::post('/fbo/status/update/{id}', [FeedBookingController::class, 'statusUpdate'])->name('Status-update');

    // Feed Order
    Route::apiResource('feed-orders', FeedOrderController::class);
    Route::get('/get-feed-order-list', [FeedOrderController::class, 'getFeedOrderList'])->name('getFeedOrderList');
    Route::get('/get-re-feed-order-list', [FeedOrderController::class, 'getFReOrderList'])->name('getFReOrderList');
    Route::get('/get-feed-data', [FeedOrderController::class, 'getFeedOrdersData']);
    Route::post('/get-feed-product-price', [FeedOrderController::class, 'getFeedProductDailyPrice']);
    Route::post('/feo/status/update/{id}', [FeedOrderController::class, 'statusUpdate'])->name('Status-update');


    //Feed Draft
    Route::apiResource('feed-drafts', FeedDraftController::class);
    Route::post('/feed-draft/status/update/{id}', [FeedDraftController::class, 'statusUpdate'])->name('Status-update');

    // Feed Delivery
    Route::apiResource('feed-deliveries',FeedDeliveryController::class);

    //feed report
    Route::get('/feed-report/all-dealers-target-vs-achievement', [FeedReportController::class, 'allDealersTargetVsAchievement']);
    Route::get('/feed-report/dealer-target-vs-achievement', [FeedReportController::class, 'dealerTargetVsAchievement']);
    Route::get('feed-report/return-qty', [FeedReportController::class, 'allDealersReturnQty']);

    // Labour info
    Route::apiResource('lab-infos',LabourInfoController::class);
    Route::post('/lbi/status/update/{id}', [LabourInfoController::class, 'statusUpdate'])->name('Status-update');
    Route::get('/get-lab-info-list', [LabourInfoController::class, 'getLabInfoList'])->name('getLabInfoList');

    // Labour details
    Route::apiResource('lab-details',LabourDetailController::class);
    Route::post('/lbd/status/update/{id}', [LabourDetailController::class, 'statusUpdate'])->name('Status-update');

    // Labour payment
    Route::apiResource('lab-payments',LabourPaymentController::class);
    Route::post('/lbp/status/update/{id}', [LabourPaymentController::class, 'statusUpdate'])->name('Status-update');
    Route::post('/labour-payment-generate', [LabourPaymentController::class, 'generatePayment'])->name('generatePayment');

    //paymentReciveReport
    Route::get('payment-receive-reports', [PaymentReceiveReportController::class, 'index']);
    Route::get('/filtered-bank-summary', [PaymentReceiveReportController::class, 'getFilteredCollectionSummary']);
    Route::get('/company-bank-summary', [PaymentReceiveReportController::class, 'getCompanyWiseBankSummary']);
    Route::get('/get-egg-bank-total', [PaymentReceiveReportController::class, 'getEggBankTotal']);
    Route::get('/get-feed-bank-total', [PaymentReceiveReportController::class, 'getFeedBankTotal']);

    //
    Route::get('get-feed-sales-total', [FeedSalesSummeryController::class, 'getFeedSalesSummary']);
    Route::get('get-egg-sales-total', [FeedSalesSummeryController::class, 'getEggSalesSummary']);
    Route::get('/sector-wise-sales-total', [FeedSalesSummeryController::class, 'getSectorWiseSalesTotal']);
    Route::get('/feed-sale-summary', [FeedSalesSummeryController::class, 'getFeedSaleSummary']);
    Route::get('/feed-order-summary-report', [FeedSalesSummeryController::class, 'getFeedOrderSummary']);
    Route::get('/feed-return-report', [FeedReportController::class, 'getFeedReturnReport']);
    Route::get('/feed-return-sum', [FeedReportController::class, 'getFeedReturnSum']);
    Route::get('/egg-return-report', [FeedReportController::class, 'getEggReturnReport']);
    Route::get('/egg-return-sum', [FeedReportController::class, 'getEggReturnSum']);

    //
    Route::apiResource('emp-sales-group',EmpSalesGroupController::class);
    Route::apiResource('emp-manage-group',EmpManageGroupController::class);


    // Chicks Section////////////////////////////////////////////////////////////////

    // Chicks Farm Production Management
    Route::apiResource('chicks-farm-productions',ChicksFarmProductionController::class);
    Route::prefix('chicks-farm-productions')->group(function () {
        Route::post('/multiStatus', [ChicksFarmProductionController::class, 'updateMultiStatus']);
        Route::get('/', [ChicksFarmProductionController::class, 'index']);
        Route::get('/{id}', [ChicksFarmProductionController::class, 'show']);
        Route::post('/', [ChicksFarmProductionController::class, 'store']);
        Route::post('/{id}/status', [ChicksFarmProductionController::class, 'updateStatus']);
        Route::delete('/{id}', [ChicksFarmProductionController::class, 'destroy']);
        Route::post('/{id}', [ChicksFarmProductionController::class, 'update']);
    });
    Route::get('/egg-settings-proxy', [ChicksFarmProductionController::class, 'getEggSettings']);
    Route::get('/check-setting-id/{settingId}', [ChicksFarmProductionController::class, 'checkSettingId']);

    //Breed management
    Route::apiResource('breeds',BreedController::class);

    // Chicks Production Ledger Management
    Route::apiResource('cp-ledgers',ChicksProductionLedgerController::class);

    //  chicks Stock Management
    Route::apiResource('chicks-stocks', controller: ChicksStockController::class);
    Route::get('/get-chicks-stock', [ChicksStockController::class, 'getChicksStock']);
    //Route::get('/get-production-ChicksStock', [ChicksStockController::class, 'getChicksProductStocksByChildCategory']);

 // Chicks StockAdjustment Management
 Route::apiResource('chicks-adjustments',ChicksStockAdjustmentController::class);
 Route::prefix('chicks-adjustments')->group(function () {
     Route::get('/', [ChicksStockAdjustmentController::class, 'index']);
     Route::get('/details/{id}', [ChicksStockAdjustmentController::class, 'getChicksAdjustmentDetails']);
     Route::post('/', [ChicksStockAdjustmentController::class, 'store']);
     Route::get('/{adjId}', [ChicksStockAdjustmentController::class, 'show']);
     Route::post('/{adjId}', [ChicksStockAdjustmentController::class, 'update']);
     Route::post('/{id}/status', [ChicksStockAdjustmentController::class, 'updateStatus']);
     Route::delete('/{id}', [ChicksStockAdjustmentController::class, 'destroy']);
 });
 Route::get('chicks-products/child-category/{id}', [ProductController::class, 'getChicksProductsByChildCategory']);
 Route::get('pro/chc-category/{id}', [ProductController::class, 'getChicksProductStockByFilters']);

 // Chicks Price Management
 Route::apiResource('chicks-price',controller: ChicksPriceController::class);
 Route::post('/chip/status/update/{id}', [ChicksPriceController::class, 'statusUpdate'])->name('Status-update');

 // Chicks Daily Price Management
 Route::apiResource('d-c-price',ChicksDailyPriceController::class);
 Route::post('/cdpr/status/update/{id}', [ChicksDailyPriceController::class, 'statusUpdate'])->name('Status-update');
 Route::apiResource('d-cp-history',ChicksDailyPriceHistoryController::class);

  //Chicks Booking
  Route::apiResource('c-bookings',ChicksBookingController::class);
  Route::get('/get-feed-book-list', [ChicksBookingController::class, 'geChicksBookList'])->name('geChicksBookList');
  Route::post('/chbo/status/update/{id}', [ChicksBookingController::class, 'statusUpdate'])->name('Status-update');


});
