<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\FinanceReportController;
use App\Http\Controllers\Admin\FootballDataSyncController;
use App\Http\Controllers\Admin\MatchResultController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\NewsAdminController;
use App\Http\Controllers\Admin\PublicPortalController;
use App\Http\Controllers\Admin\SettlementController;
use App\Http\Controllers\ApiPeriodController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\JoinController;
use App\Http\Controllers\LiveController;
use App\Http\Controllers\MatchController;
use App\Http\Controllers\NewsController as UserNewsController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PublicSiteController;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\RankingController;
use App\Services\UnauthorizedAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicSiteController::class, 'home'])->name('public.home');
Route::get('/news', [PublicSiteController::class, 'news'])->name('public.news');
Route::get('/news/{article:slug}', [PublicSiteController::class, 'show'])->name('public.news.show');
Route::get('/category/{category:slug}', [PublicSiteController::class, 'category'])->name('public.category');
Route::get('/videos', [PublicSiteController::class, 'videos'])->name('public.videos');
Route::get('/live-scores', [PublicSiteController::class, 'liveScores'])->name('public.live-scores');
Route::get('/fixtures', [PublicSiteController::class, 'fixtures'])->name('public.fixtures');
Route::get('/teams', [PublicSiteController::class, 'teams'])->name('public.teams');
Route::get('/search', [PublicSiteController::class, 'search'])->name('public.search');
Route::get('/cup', [PublicSiteController::class, 'cup'])->name('public.cup');

Route::get('/join/{code}', JoinController::class)->name('join');
Route::get('/payment/callback/zibal', fn () => abort(404))->name('payment.callback.zibal');
Route::get('/auth', [AuthController::class, 'show'])->name('login');
Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');

Route::middleware('invite.access')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/matches', [MatchController::class, 'index'])->name('matches.index');
    Route::get('/matches/{match}', [MatchController::class, 'show'])->name('matches.show');
    Route::get('/live/{match}', [LiveController::class, 'show'])->name('live.show');
    Route::get('/api/matches/{match}/live-status', [LiveController::class, 'status'])->name('api.matches.live-status');
    Route::get('/api/dashboard/live-summary', [LiveController::class, 'dashboardSummary'])->name('api.dashboard.live-summary');
    Route::get('/ranking/period/{period}', [ApiPeriodController::class, 'ranking'])->name('ranking.period');
    Route::get('/settlements/period/{period}', [ApiPeriodController::class, 'settlement'])->name('settlements.period');
    Route::get('/payment/result/{transaction}', [PaymentController::class, 'result'])->name('payment.result');
    Route::post('/matches/{match}/prediction/preview', [PredictionController::class, 'preview'])->name('matches.prediction.preview');
    Route::post('/matches/{match}/prediction', [PredictionController::class, 'store'])->name('matches.prediction.store');
    Route::post('/predictions/{entry}/pay', [PaymentController::class, 'pay'])->name('predictions.pay');
    Route::get('/ranking', [RankingController::class, 'index'])->name('ranking');
    Route::get('/settlements', [RankingController::class, 'settlements'])->name('settlements');
    Route::get('/invite', [InviteController::class, 'index'])->name('invite');
    Route::get('/dashboard/news/{article:slug}', [UserNewsController::class, 'show'])->name('news.show');
});


Route::middleware(['auth', 'admin'])->prefix('news-admin')->name('news-admin.')->group(function () {
    Route::get('/', [NewsAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/settings', [NewsAdminController::class, 'settings'])->name('settings');
    Route::post('/settings/site', [NewsAdminController::class, 'updateSiteSettings'])->name('settings.site.update');
    Route::post('/settings/news', [NewsAdminController::class, 'updateNewsSettings'])->name('settings.news.update');
    Route::post('/settings/footer', [NewsAdminController::class, 'updateFooterSettings'])->name('settings.footer.update');
    Route::get('/news', [NewsController::class, 'index'])->name('news.index');
    Route::post('/news/sync', [NewsController::class, 'sync'])->name('news.sync');
    Route::post('/news/test-gemini', [NewsController::class, 'testGemini'])->name('news.test-gemini');
    Route::post('/news/test-llm7', [NewsController::class, 'testLlm7'])->name('news.test-llm7');
    Route::post('/news/test-microsoft', [NewsController::class, 'testMicrosoft'])->name('news.test-microsoft');
    Route::patch('/news/{article}', [NewsController::class, 'update'])->name('news.update');
    Route::delete('/news/{article}', [NewsController::class, 'destroy'])->name('news.destroy');
    Route::get('/categories', [PublicPortalController::class, 'categories'])->name('public.categories');
    Route::post('/categories', [PublicPortalController::class, 'storeCategory'])->name('public.categories.store');
    Route::patch('/categories/{category}', [PublicPortalController::class, 'updateCategory'])->name('public.categories.update');
    Route::get('/ads', [PublicPortalController::class, 'ads'])->name('public.ads');
    Route::post('/ads', [PublicPortalController::class, 'storeAd'])->name('public.ads.store');
    Route::patch('/ads/{ad}', [PublicPortalController::class, 'updateAd'])->name('public.ads.update');
    Route::delete('/ads/{ad}', [PublicPortalController::class, 'destroyAd'])->name('public.ads.destroy');
    Route::get('/sources', [PublicPortalController::class, 'sources'])->name('public.sources');
    Route::patch('/sources/{source}', [PublicPortalController::class, 'updateSource'])->name('public.sources.update');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::get('/matches', [AdminController::class, 'page'])->defaults('page', 'matches')->name('matches');
    Route::get('/matches/{match}/edit-result', [MatchResultController::class, 'edit'])->name('matches.edit-result');
    Route::post('/matches/{match}/result', [MatchResultController::class, 'result'])->name('matches.result');
    Route::post('/matches/{match}/status', [MatchResultController::class, 'status'])->name('matches.status');
    Route::post('/matches/{match}/events', [MatchResultController::class, 'event'])->name('matches.events');
    Route::post('/matches/{match}/calculate-score', [MatchResultController::class, 'calculate'])->name('matches.calculate-score');
    Route::get('/users', [AdminController::class, 'page'])->defaults('page', 'users')->name('users');
    Route::patch('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::get('/predictions', [AdminController::class, 'page'])->defaults('page', 'predictions')->name('predictions');
    Route::get('/payments', [AdminController::class, 'page'])->defaults('page', 'payments')->name('payments');
    Route::post('/payment-transactions/{transaction}/approve', [AdminController::class, 'approvePayment'])->name('payment-transactions.approve');
    Route::post('/payment-transactions/{transaction}/reject', [AdminController::class, 'rejectPayment'])->name('payment-transactions.reject');
    Route::get('/settlements', [AdminController::class, 'page'])->defaults('page', 'settlements')->name('settlements');
    Route::get('/settlements/{period}', [SettlementController::class, 'show'])->name('settlements.show');
    Route::post('/settlements/{period}/calculate', [SettlementController::class, 'calculate'])->name('settlements.calculate');
    Route::post('/settlements/{period}/finalize', [SettlementController::class, 'finalize'])->name('settlements.finalize');
    Route::post('/settlements/{period}/mark-paid', [SettlementController::class, 'markPaid'])->name('settlements.mark-paid');
    Route::get('/settlements/{period}/export', [SettlementController::class, 'export'])->name('settlements.export');
    Route::get('/finance/report', FinanceReportController::class)->name('finance.report');
    Route::get('/football-data', [FootballDataSyncController::class, 'index'])->name('football-data.index');
    Route::post('/football-data/sync', [FootballDataSyncController::class, 'run'])->name('football-data.sync');
    Route::get('/news', [NewsController::class, 'index'])->name('news.index');
    Route::post('/news/sync', [NewsController::class, 'sync'])->name('news.sync');
    Route::post('/news/test-gemini', [NewsController::class, 'testGemini'])->name('news.test-gemini');
    Route::post('/news/test-llm7', [NewsController::class, 'testLlm7'])->name('news.test-llm7');
    Route::post('/news/test-microsoft', [NewsController::class, 'testMicrosoft'])->name('news.test-microsoft');
    Route::patch('/news/{article}', [NewsController::class, 'update'])->name('news.update');
    Route::delete('/news/{article}', [NewsController::class, 'destroy'])->name('news.destroy');
    Route::get('/referrals', [AdminController::class, 'page'])->defaults('page', 'referrals')->name('referrals');
    Route::post('/invite-links', [AdminController::class, 'storeInvite'])->name('invite-links.store');
    Route::patch('/invite-links/{invite}', [AdminController::class, 'updateInvite'])->name('invite-links.update');
    Route::delete('/invite-links/{invite}', [AdminController::class, 'destroyInvite'])->name('invite-links.destroy');
    Route::get('/public/categories', [PublicPortalController::class, 'categories'])->name('public.categories');
    Route::post('/public/categories', [PublicPortalController::class, 'storeCategory'])->name('public.categories.store');
    Route::patch('/public/categories/{category}', [PublicPortalController::class, 'updateCategory'])->name('public.categories.update');
    Route::get('/public/ads', [PublicPortalController::class, 'ads'])->name('public.ads');
    Route::post('/public/ads', [PublicPortalController::class, 'storeAd'])->name('public.ads.store');
    Route::patch('/public/ads/{ad}', [PublicPortalController::class, 'updateAd'])->name('public.ads.update');
    Route::delete('/public/ads/{ad}', [PublicPortalController::class, 'destroyAd'])->name('public.ads.destroy');
    Route::get('/public/sources', [PublicPortalController::class, 'sources'])->name('public.sources');
    Route::patch('/public/sources/{source}', [PublicPortalController::class, 'updateSource'])->name('public.sources.update');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::post('/settings/general', [AdminController::class, 'updateGeneralSettings'])->name('settings.general.update');
    Route::post('/settings/football-data', [AdminController::class, 'updateFootballDataSettings'])->name('settings.football-data.update');
    Route::post('/settings/news', [AdminController::class, 'updateNewsSettings'])->name('settings.news.update');
    Route::post('/settings/payment-gateway', [AdminController::class, 'updatePaymentGatewaySettings'])->name('settings.payment-gateway.update');
    Route::get('/settings/finance', [AdminController::class, 'finance'])->name('settings.finance');
    Route::post('/settings/finance', [AdminController::class, 'updateFinance'])->name('settings.finance.update');
});
