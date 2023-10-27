<?php

use App\Http\Controllers\AboutUsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AsigneCaseController;
use App\Http\Controllers\attorneyController;
use App\Http\Controllers\CaseActionController;
use App\Http\Controllers\caseController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\CredentialLoggerController;
use App\Http\Controllers\DueAsignementsController;
use App\Http\Controllers\Faq\FaqController;
use App\Http\Controllers\FireBaseController;
use App\Http\Controllers\FlagController;
use App\Http\Controllers\GuidelineController;
use App\Http\Controllers\HearingController;
use App\Http\Controllers\LawcaseController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MyFatoorahController;
use App\Http\Controllers\NotificationDataController;
use App\Http\Controllers\PendingApprovalController;
use App\Http\Controllers\Percentage\AsignePercentageController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\ResponseController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TermsController;
use App\Http\Controllers\uploadResponse;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Voucher\VoucherController;
use App\Http\Controllers\WelcomePageController;


/////login and authentication routes here
Route::group(['prefix' => 'auth'], function () {
    Route::get('{type}/login/{token}', [LoginController::class, 'socialLogin'])->name('social.login');
    Route::post('signup', [RegistrationController::class, 'userRegistration'])->name('registration');
    Route::post('/login', [LoginController::class, 'login'])->name('login');
    Route::get('/reset/password/{reset_link_type}/{email_or_phone}', [LoginController::class, 'resetPassordOtpSend'])->name('reset.password.otp');
    Route::post('/reset/password', [LoginController::class, 'resetPassword'])->name('reset.password');
});
///////////authentication routes end here///////////

///////////user part start from here///////////////////////////////
Route::group(['prefix' => 'user', 'middleware' => ['jwt']], function () {
    Route::post('/create/case', [caseController::class, 'createCase'])->name('create.case');
    Route::get('myschedule', [UserController::class, 'clientMettingSchedule'])->name('schedule.list');
    Route::post('/extra/service', [caseController::class, 'extraServiceCreate'])->name('create.extraservice');
    Route::get('service-list', [UserController::class, 'serviceList'])->name('service.list');
    //Route::get('/delete/case/{case_id}',[UserController::class,'delete_case']);
    Route::post('initiate-payment', [MyFatoorahController::class, 'index'])->name('myfatoorah.pay');
});

Route::get('payment-callback', [MyFatoorahController::class, 'callback'])->name('callback.myfatoorah');
Route::get('payment-callback-response', [MyFatoorahController::class, 'callbackResponse'])->name('callback.response.myfatoorah');
///////////user part start end here///////////////////////////////

///////////admin part start from here///////////////////////////////
Route::group(['prefix' => 'admin', 'middleware' => ['admin']], function () {

    Route::get('home', [AdminController::class, 'hommeData'])->name('admin.home');
    Route::get('/all/user', [AdminController::class, 'allUser'])->name('list.users');
    Route::get('/all/attorney', [AdminController::class, 'allAttorney'])->name('list.attorneys');
    Route::post('create/admin', [RegistrationController::class, 'adminRegistration'])->name('create.coadmin');
    Route::get('/cases/user/{uid}', [AdminController::class, 'userCases'])->name('single.user.cases');
    Route::get('/seach/{code}', [AdminController::class, 'searchByCode'])->name('search.by.code');
    Route::post('/asigne/case', [AsigneCaseController::class, 'asigneAttorneyToCase'])->name('asign.attorney.case');
    //Route::get('/order/list',[AdminController::class,'orderList']);
    //Route::post('/edit/price/{list_id}',[AdminController::class,'editPrice']);
    Route::post('/create/attorney', [RegistrationController::class, 'attorneyRegistration'])->name('create.attorney');
    Route::post('hearing/list', [HearingController::class, 'hearingList'])->name('list.hearings');
    Route::post('action/list', [CaseActionController::class, 'adminActionList'])->name('list.actions');
    Route::get('/delete/attorney/{attorney}', [AdminController::class, 'deleteAttorney'])->name('delete.attorney');
    Route::get('delete/case/response/{fileId}', [uploadResponse::class, 'delteResponseFiles'])->name('delete.response.file');
    Route::post('remove/attorney', [AsigneCaseController::class, 'removeAttorney'])->name('remove.attorney.case');
    Route::post('/update/terms', [TermsController::class, 'updateTerm'])->name('update.term');

    //servie rout elist
    Route::get('all/{type}/list', [caseController::class, 'adminServiceList'])->name('admin.service.list');

    //voucher
    Route::post('create/voucher', [VoucherController::class, 'createVoucher'])->name('create.voucher');
    Route::post('update/voucher', [VoucherController::class, 'voucherUpdate'])->name('update.voucher');
    Route::delete('delete/voucher/{voucher}', [VoucherController::class, 'deleteVoucher'])->name('delete.voucher');
    Route::post('voucher-list', [VoucherController::class, 'voucherList'])->name('voucher.list');

    //reminders
    Route::post('create/reminder', [ReminderController::class, 'createReminder'])->name('create.reminder');
    Route::delete('reminder/{reminders}', [ReminderController::class, 'deleteReminder'])->name('reminder.delete');

    //response file accept reject part
    Route::post('accept/response/file', [ResponseController::class, 'acceptResponseFile'])->name('accept.response.file');
    Route::post('reject/response/file', [ResponseController::class, 'rejectReponseFile'])->name('reject.response.file');

    //flag part
    Route::post('flag/user', [FlagController::class, 'disableUser'])->name('flag.user');
    Route::get('remove/flag/user/{user}', [FlagController::class, 'removeUserFromFlag'])->name('remove.flag.user');

    //create sub category with price
    Route::post('create/sub-category', [CategoryController::class, 'createCaseSubcategory'])->name('create.sub.category');
    Route::get('get/category-list', [CategoryController::class, 'getCategoryList'])->name('categories.list');
    Route::post('update/sub-category', [CategoryController::class, 'updateSubCategory'])->name('update.sub.category');
    Route::delete('delete/sub-category/{category_id}', [CategoryController::class, 'deleteSubCategory'])->name('category.delete');

    //case
    Route::get('update-case-status/{status_name}/{case_id}', [caseController::class, 'updateCaseStatus'])->name('update.case.status');

    //delete user
    Route::delete('user/{user}', [AdminController::class, 'deleteUser'])->name('delete.user');

    //update about
    Route::post('/update/about', [AboutUsController::class, 'aboutUpdate'])->name('update.about');

    //admin asigne their percentage on attorney
    Route::post('attorney/assign-percentage', [AsignePercentageController::class, 'assigneAttorneyPercentage'])->name('create.asigne.percentage');
    Route::post('attorney/update-percentage', [AsignePercentageController::class, 'updateAssignAttorneyPercentage'])->name('update.asign.percentage');

    //welcome page
    Route::post('create/welcome-page', [WelcomePageController::class, 'createWelcomePage'])->name('create.welcome.page');
    Route::delete('welcome-page/{WelcomePage}', [WelcomePageController::class, 'deleteWelcomePage'])->name('delete.welcomepage');
    Route::post('/update/welcome-page', [WelcomePageController::class, 'updateWelcomePage'])->name('update.welcome.page');

    Route::get('pending-approval', [PendingApprovalController::class, 'adminPendingApproval'])->name('admin.pending.approval.list');
    Route::get('due-asignements', [DueAsignementsController::class, 'adminDueAsignementsList'])->name('admin.due.asignments');

    ////guidelines routes
    Route::post('create/guideline', [GuidelineController::class, 'createGuideline'])->name('create.guideline');
    Route::post('update/guideline', [GuidelineController::class, 'updateGuideline'])->name('update.guideline');

    //admin login route
    Route::post('login', [LoginController::class, 'adminLogin'])->withoutMiddleware('admin')->name('admin.login');

    //faq part
    Route::post('create-faq', [FaqController::class, 'createFaq'])->name('create.faq');
    Route::post('update-faq', [FaqController::class, 'updateFaqData'])->name('update.faq');
    Route::delete('delete-faq/{faq}', [FaqController::class, 'deleteFaq'])->name('delte.faq');

    //attorney reset password
    Route::post('attorney-credentails-reset', [UserController::class, 'resetAttorneyCredentails'])->name('attorney.reset.credentials');
    Route::get('credential-mailer/{user_id}', [CredentialLoggerController::class, 'sendCredentail'])->name('send.credential');
});
///////////admin part end here///////////////////////////////


///////////attorney part start from here///////////////////////////////
Route::group(['prefix' => 'attorney', 'middleware' => ['attorney']], function () {
    //Route::get('profile',[attorneyController::class,'profile']);
    // Route::get('mycases',[attorneyController::class,'mytasks']);
    Route::post('hearings-list', [HearingController::class, 'getAttorneyHearingList'])->name('attorney.hearing.list');
    Route::get('pending/approval', [PendingApprovalController::class, 'attorneyPendingApproval'])->name('attorney.pending.approval');
    Route::get('due/assignments', [attorneyController::class, 'dueAssignments'])->name('attorney.due.asignements');
    Route::post('action-list', [CaseActionController::class, 'attorneyActions'])->name('attorney.actions.list');
    Route::get('view/{type}', [attorneyController::class, 'attorneyDataView'])->name('attorney.data.view');
    Route::post('login', [LoginController::class, 'attorneyLogin'])->withoutMiddleware('attorney')->name('attorney.login');
});
///////////attorney part start ebd here///////////////////////////////


////////attorney and admin part here///////////

Route::group(['middleware' => 'admin_attorney'], function () {
    Route::post('/add/hearings', [HearingController::class, 'addHearings'])->name('create.hearing');
    Route::post('/update/hearings', [HearingController::class, 'updateHearing'])->name('update.hearing');
    Route::post('/add/action', [CaseActionController::class, 'createAction'])->name('create.action');
    Route::get('/delete/action/{actionID}', [CaseActionController::class, 'deleteAction'])->name('delete.action');
    Route::post('update/action', [CaseActionController::class, 'updateAction'])->name('update.action');
    Route::post('upload/case/response', [uploadResponse::class, 'UploadCaseResponse'])->name('upload.case.response');
    Route::get('delete/hearing/{hearing_id}', [attorneyController::class, 'deleteHearings'])->name('delete.hearing');
    Route::get('update/action/status/{action_type}/{actionID}', [attorneyController::class, 'updateActionStatus'])->name('update.action.status');
    Route::post('notify-user', [FireBaseController::class, 'notification'])->name('notification');
    Route::post('update/reminder-status/{reminders}', [ReminderController::class, 'updateReminderStatus'])->name('update.status.reminder');

    //reminder list
    Route::post('reminder/list', [ReminderController::class, 'reminderList'])->name('reminder.list');

    //profile part view
    Route::get('view/{type}/{attorney}', [ProfileController::class, 'attorneyDataView'])->name('data.view.admin');

    Route::post('common/advance/search', [SearchController::class, 'advanceSearch'])->name('advance.search');
});

/////////////user Admin Part/////////////////
Route::group(['middleware' => 'userAdmin'], function () {
    //Route::get('delete/case/file/{case_file_id}',[AdminController::class,'deleteCaseFiles']);
    Route::post('update/case', [caseController::class, 'updateCaseAdminUser'])->name('update.case');
    Route::delete('delete/case/{case_id}', [LawcaseController::class, 'deleteCase'])->name('delete.case');
    Route::get('all/admins', [AdminController::class, 'allAdmins'])->name('list.admins');
});

///////////common route part start from here///////////////////////////////

Route::group(['prefix' => 'common', 'middleware' => ['common']], function () {
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    // Route::get('delete',[UserController::class,'deleteUser']);
    Route::get('/case/details/{case_id}', [caseController::class, 'viewCase'])->name('case.view');
    Route::get('price/list', [PriceController::class, 'priceList'])->name('list.price');
    Route::get('myprofile', [UserController::class, 'profile'])->name('profile');
    Route::get('other/profile/{user}', [ProfileController::class, 'otherProfile'])->name('other.profile'); //[UserController::class,'otherProfiles']);
    Route::post('/upload/image', [ProfileController::class, 'uploadProfileImage'])->name('upload.profile.image');
    Route::post('reset/password', [UserController::class, 'userPasswordReset'])->name('user.password.reset');

    Route::post('store/voice', [caseController::class, 'storeVoice'])->name('store.case.voice');
    Route::post('/update/profile', [UserController::class, 'updateProfile'])->name('update.profile');
});

Route::group(['middleware' => ['common']], function () {
    Route::delete('delete/account', [UserController::class, 'deleteAccount'])->name('delete.account');
    Route::get('/all/cases/{type}', [caseController::class, 'allCases'])->name('list.cases');
    Route::get('home', [UserController::class, 'home'])->name('user.home.data');
    Route::delete('/delete/profile-photo', [UserController::class, 'DeletePhoto'])->name('delete.profile.data');
    Route::post('delete-files', [CommonController::class, 'deleteFiles'])->name('delete.files');
    Route::post('upload-case-files', [CommonController::class, 'uploadSeparateFiles'])->name('case.upload.separate.file');
    Route::get('view/user/orders/{type}/{user}', [ProfileController::class, 'viewUserOrder'])->name('view.user.cases');
    Route::get('notifications', [NotificationDataController::class, 'notificationList'])->name('notification.list');
});

Route::get('/about/{user_type}', [AboutUsController::class, 'aboutUs'])->name('aboutus');
Route::get('/terms/{user_type}', [TermsController::class, 'termsandcondition'])->name('terms');
Route::get('/{user_type}/welcome', [WelcomePageController::class, 'getWelcomePage'])->name('welcome.page.data');
Route::get('reset-password/otp-checker/{otp}', [LoginController::class, 'otpCheck'])->name('otp.checker');
Route::get('account-verification/{otp}', [LoginController::class, 'accountVerification'])->name('account.verify');
Route::get('settings', [WelcomePageController::class, 'settingPage'])->name('settings');
//Route::post('sms-test',[LoginController::class, 'smsTest']);
Route::get('guideline-list', [GuidelineController::class, 'guidelineList'])->name('guideline.list');

Route::get('test-notification', [AdminController::class, 'testNotification'])->name('test.fcm');

//faq list 
Route::post('faq-list', [FaqController::class, 'faqList'])->name('faq.list');

///////////common route part start end here///////////////////////////////
