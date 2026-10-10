<?php

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\AbuseTypeController;
use App\Http\Controllers\Admin\AwarenessArticleController;
use App\Http\Controllers\Admin\AwarenessCategoryController;
use App\Http\Controllers\Admin\ContactMessageController as AdminContactMessageController;
use App\Http\Controllers\Admin\FaqCategoryController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\ManagedSupportCaseController as AdminManagedSupportCaseController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SupportOfficerController;
use App\Http\Controllers\Admin\SupportResourceController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AnonymousSupportCaseController;
use App\Http\Controllers\CaseAttachmentController;
use App\Http\Controllers\CaseMessageController;
use App\Http\Controllers\CaseNoteController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Officer\ManagedSupportCaseController as OfficerManagedSupportCaseController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\SupportCaseController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicPageController::class, 'home'])->name('home');
Route::get('/about', [PublicPageController::class, 'about'])->name('public.about');
Route::get('/types-of-abuse', [PublicPageController::class, 'types'])->name('public.types');
Route::get('/warning-signs', [PublicPageController::class, 'warningSigns'])->name('public.warning-signs');
Route::get('/safety', [PublicPageController::class, 'safety'])->name('public.safety');
Route::get('/awareness', [PublicPageController::class, 'awareness'])->name('public.awareness');
Route::get('/awareness/{slug}', [PublicPageController::class, 'article'])->name('public.awareness.show');
Route::get('/resources', [PublicPageController::class, 'resources'])->name('public.resources');
Route::get('/faqs', [PublicPageController::class, 'faqs'])->name('public.faqs');
Route::get('/get-support', [PublicPageController::class, 'getSupport'])->name('public.get-support');
Route::get('/contact', [PublicPageController::class, 'contact'])->name('public.contact');
Route::post('/contact', [ContactMessageController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('public.contact.store');
Route::get('/quick-exit', [PublicPageController::class, 'quickExit'])->name('public.quick-exit');
Route::get('/anonymous-support', [AnonymousSupportCaseController::class, 'create'])->name('anonymous.create');
Route::post('/anonymous-support', [AnonymousSupportCaseController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('anonymous.store');
Route::get('/anonymous-support/confirmation', [AnonymousSupportCaseController::class, 'confirmation'])->name('anonymous.confirmation');
Route::get('/anonymous-support/track', [AnonymousSupportCaseController::class, 'trackForm'])->name('anonymous.track.form');
Route::post('/anonymous-support/track', [AnonymousSupportCaseController::class, 'lookup'])
    ->middleware('throttle:5,1')
    ->name('anonymous.track.lookup');

Route::middleware(['auth', 'verified', 'active'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'user'])
        ->middleware('role:'.User::ROLE_USER)
        ->name('dashboard');

    Route::post('cases/{support_case}/messages', [CaseMessageController::class, 'store'])
        ->name('cases.messages.store');

    Route::post('cases/{support_case}/attachments', [CaseAttachmentController::class, 'store'])
        ->name('cases.attachments.store');

    Route::get('case-attachments/{case_attachment}/download', [CaseAttachmentController::class, 'download'])
        ->name('case-attachments.download');

    Route::post('cases/{support_case}/notes', [CaseNoteController::class, 'store'])
        ->name('cases.notes.store');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/{notification}', [NotificationController::class, 'open'])->name('notifications.open');
    Route::patch('notifications', [NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');

    Route::resource('cases', SupportCaseController::class)
        ->only(['index', 'create', 'store', 'show'])
        ->parameters(['cases' => 'support_case'])
        ->middleware('role:'.User::ROLE_USER);

    Route::get('/officer/dashboard', [DashboardController::class, 'officer'])
        ->middleware('role:'.User::ROLE_SUPPORT_OFFICER)
        ->name('officer.dashboard');

    Route::prefix('officer')
        ->name('officer.')
        ->middleware('role:'.User::ROLE_SUPPORT_OFFICER)
        ->group(function () {
            Route::get('cases', [OfficerManagedSupportCaseController::class, 'index'])->name('cases.index');
            Route::get('cases/{support_case}', [OfficerManagedSupportCaseController::class, 'show'])->name('cases.show');
            Route::patch('cases/{support_case}', [OfficerManagedSupportCaseController::class, 'update'])->name('cases.update');
            Route::patch('cases/{support_case}/accept', [OfficerManagedSupportCaseController::class, 'accept'])->name('cases.accept');
        });

    Route::get('/admin/dashboard', [DashboardController::class, 'admin'])
        ->middleware('role:'.User::ROLE_ADMIN)
        ->name('admin.dashboard');

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('role:'.User::ROLE_ADMIN)
        ->group(function () {
            Route::resource('awareness/categories', AwarenessCategoryController::class)
                ->parameters(['categories' => 'awareness_category'])
                ->names('awareness.categories');

            Route::patch('awareness/articles/{awareness_article}/publish', [AwarenessArticleController::class, 'publish'])
                ->name('awareness.articles.publish');
            Route::patch('awareness/articles/{awareness_article}/archive', [AwarenessArticleController::class, 'archive'])
                ->name('awareness.articles.archive');
            Route::resource('awareness/articles', AwarenessArticleController::class)
                ->parameters(['articles' => 'awareness_article'])
                ->names('awareness.articles');

            Route::resource('resources', SupportResourceController::class)
                ->parameters(['resources' => 'support_resource']);

            Route::patch('abuse-types/{abuse_type}/activate', [AbuseTypeController::class, 'activate'])
                ->name('abuse-types.activate');
            Route::patch('abuse-types/{abuse_type}/deactivate', [AbuseTypeController::class, 'deactivate'])
                ->name('abuse-types.deactivate');
            Route::resource('abuse-types', AbuseTypeController::class)
                ->except('show')
                ->parameters(['abuse-types' => 'abuse_type']);

            Route::patch('faq-categories/{faq_category}/activate', [FaqCategoryController::class, 'activate'])
                ->name('faq-categories.activate');
            Route::patch('faq-categories/{faq_category}/deactivate', [FaqCategoryController::class, 'deactivate'])
                ->name('faq-categories.deactivate');
            Route::patch('faq-categories/{faq_category}/move-up', [FaqCategoryController::class, 'moveUp'])
                ->name('faq-categories.move-up');
            Route::patch('faq-categories/{faq_category}/move-down', [FaqCategoryController::class, 'moveDown'])
                ->name('faq-categories.move-down');
            Route::resource('faq-categories', FaqCategoryController::class)
                ->except('show')
                ->parameters(['faq-categories' => 'faq_category']);

            Route::patch('faqs/{faq}/activate', [FaqController::class, 'activate'])
                ->name('faqs.activate');
            Route::patch('faqs/{faq}/deactivate', [FaqController::class, 'deactivate'])
                ->name('faqs.deactivate');
            Route::patch('faqs/{faq}/move-up', [FaqController::class, 'moveUp'])
                ->name('faqs.move-up');
            Route::patch('faqs/{faq}/move-down', [FaqController::class, 'moveDown'])
                ->name('faqs.move-down');
            Route::resource('faqs', FaqController::class)
                ->except('show');

            Route::resource('contact-messages', AdminContactMessageController::class)
                ->only(['index', 'show', 'update', 'destroy']);

            Route::patch('users/{user}/activate', [UserController::class, 'activate'])
                ->name('users.activate');
            Route::patch('users/{user}/deactivate', [UserController::class, 'deactivate'])
                ->name('users.deactivate');
            Route::resource('users', UserController::class)
                ->only(['index', 'show']);

            Route::patch('support-officers/{support_officer}/activate', [SupportOfficerController::class, 'activate'])
                ->name('support-officers.activate');
            Route::patch('support-officers/{support_officer}/deactivate', [SupportOfficerController::class, 'deactivate'])
                ->name('support-officers.deactivate');
            Route::resource('support-officers', SupportOfficerController::class)
                ->except('destroy');

            Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');

            Route::get('settings', [SystemSettingController::class, 'edit'])->name('settings.edit');
            Route::patch('settings', [SystemSettingController::class, 'update'])->name('settings.update');

            Route::resource('audit-logs', AuditLogController::class)
                ->only(['index', 'show']);

            Route::get('cases', [AdminManagedSupportCaseController::class, 'index'])->name('cases.index');
            Route::get('cases/{support_case}', [AdminManagedSupportCaseController::class, 'show'])->name('cases.show');
            Route::patch('cases/{support_case}', [AdminManagedSupportCaseController::class, 'update'])->name('cases.update');
        });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
