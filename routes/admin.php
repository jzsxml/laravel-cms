<?php

use App\Http\Controllers\Admin\AjaxController;
use App\Http\Controllers\Admin\Auth\AdminController as AuthAdminController;
use App\Http\Controllers\Admin\Auth\GroupController;
use App\Http\Controllers\Admin\Auth\RuleController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\Cms\ArchivesController;
use App\Http\Controllers\Admin\Cms\BlockController;
use App\Http\Controllers\Admin\Cms\CommentController;
use App\Http\Controllers\Admin\Cms\ModelDataController;
use App\Http\Controllers\Admin\Cms\ModelDesignerController;
use App\Http\Controllers\Admin\Cms\OrderController as CmsOrderController;
use App\Http\Controllers\Admin\Cms\PageController as CmsPageController;
use App\Http\Controllers\Admin\Cms\SiteSettingsController;
use App\Http\Controllers\Admin\Cms\SpecialController;
use App\Http\Controllers\Admin\Cms\StatisticsController;
use App\Http\Controllers\Admin\Cms\TagController as CmsTagController;
use App\Http\Controllers\Admin\General\AddonController;
use App\Http\Controllers\Admin\General\AdminLogController;
use App\Http\Controllers\Admin\General\ApidocController;
use App\Http\Controllers\Admin\General\AttachmentController;
use App\Http\Controllers\Admin\General\CacheController;
use App\Http\Controllers\Admin\General\CategoryController;
use App\Http\Controllers\Admin\General\ConfigController;
use App\Http\Controllers\Admin\General\DevCommandController;
use App\Http\Controllers\Admin\General\MemberController;
use App\Http\Controllers\Admin\General\ProfileController;
use App\Http\Controllers\Admin\General\SmsController;
use App\Http\Controllers\Admin\IndexController;
use Illuminate\Support\Facades\Route;

$adminPath = config('cms.admin_path', 'admin');

Route::middleware(['web', 'admin.locale'])->prefix($adminPath)->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:cms-login')->name('login.post');

    Route::middleware(['admin.auth', 'admin.permission'])->group(function () {
        Route::get('/', [IndexController::class, 'index'])->name('dashboard');
        Route::get('index', [IndexController::class, 'index']);
        Route::get('index/menu', [IndexController::class, 'menu'])->name('menu');
        Route::post('index/logout', [IndexController::class, 'logout'])->name('logout');

        Route::post('ajax/upload', [AjaxController::class, 'upload'])->name('ajax.upload');
        Route::post('ajax/upload/chunk', [AjaxController::class, 'uploadChunk'])->name('ajax.uploadchunk');
        Route::post('ajax/upload/merge', [AjaxController::class, 'uploadMerge'])->name('ajax.uploadmerge');
        Route::get('ajax/selectpage', [AjaxController::class, 'selectpage'])->name('ajax.selectpage');

        Route::get('auth/admin', [AuthAdminController::class, 'index'])->name('auth.admin');
        Route::get('auth/admin/add', [AuthAdminController::class, 'add']);
        Route::post('auth/admin/add', [AuthAdminController::class, 'add']);
        Route::get('auth/admin/edit', [AuthAdminController::class, 'edit']);
        Route::post('auth/admin/edit', [AuthAdminController::class, 'edit']);
        Route::post('auth/admin/del', [AuthAdminController::class, 'del']);

        Route::get('auth/rule', [RuleController::class, 'index'])->name('auth.rule');
        Route::get('auth/rule/add', [RuleController::class, 'add']);
        Route::post('auth/rule/add', [RuleController::class, 'add']);
        Route::get('auth/rule/edit', [RuleController::class, 'edit']);
        Route::post('auth/rule/edit', [RuleController::class, 'edit']);
        Route::post('auth/rule/del', [RuleController::class, 'del']);
        Route::get('auth/group', [GroupController::class, 'index'])->name('auth.group');
        Route::get('auth/group/add', [GroupController::class, 'add']);
        Route::post('auth/group/add', [GroupController::class, 'add']);
        Route::get('auth/group/edit', [GroupController::class, 'edit']);
        Route::post('auth/group/edit', [GroupController::class, 'edit']);
        Route::post('auth/group/del', [GroupController::class, 'del']);

        Route::get('general/category', [CategoryController::class, 'index'])->name('general.category');
        Route::get('general/category/add', [CategoryController::class, 'add']);
        Route::post('general/category/add', [CategoryController::class, 'add']);
        Route::get('general/category/edit', [CategoryController::class, 'edit']);
        Route::post('general/category/edit', [CategoryController::class, 'edit']);
        Route::post('general/category/del', [CategoryController::class, 'del']);
        Route::post('general/category/multi', [CategoryController::class, 'multi']);

        Route::get('general/config', [ConfigController::class, 'index'])->name('general.config');
        Route::get('general/config/add', [ConfigController::class, 'add']);
        Route::post('general/config/add', [ConfigController::class, 'add']);
        Route::get('general/config/edit', [ConfigController::class, 'edit']);
        Route::post('general/config/edit', [ConfigController::class, 'edit']);
        Route::post('general/config/del', [ConfigController::class, 'del']);
        Route::post('general/config/save', [ConfigController::class, 'save'])->name('general.config.save');
        Route::get('general/attachment', [AttachmentController::class, 'index'])->name('general.attachment');
        Route::post('general/attachment/del', [AttachmentController::class, 'del']);
        Route::get('general/profile', [ProfileController::class, 'index'])->name('profile');
        Route::post('general/profile', [ProfileController::class, 'index']);
        Route::get('general/member', [MemberController::class, 'index'])->name('general.member');
        Route::get('general/member/add', [MemberController::class, 'add']);
        Route::post('general/member/add', [MemberController::class, 'add']);
        Route::get('general/member/edit', [MemberController::class, 'edit']);
        Route::post('general/member/edit', [MemberController::class, 'edit']);
        Route::post('general/member/del', [MemberController::class, 'del']);
        Route::post('general/member/multi', [MemberController::class, 'multi']);
        Route::get('general/adminlog', [AdminLogController::class, 'index'])->name('general.adminlog');

        Route::get('general/cache', [CacheController::class, 'index'])->name('general.cache');
        Route::post('general/cache/refresh', [CacheController::class, 'refresh']);

        Route::get('general/addon', [AddonController::class, 'index'])->name('general.addon');
        Route::post('general/addon/install', [AddonController::class, 'install']);
        Route::post('general/addon/uninstall', [AddonController::class, 'uninstall']);
        Route::post('general/addon/enable', [AddonController::class, 'enable']);
        Route::post('general/addon/disable', [AddonController::class, 'disable']);

        Route::get('general/command', [DevCommandController::class, 'index'])->name('general.command');
        Route::post('general/command/run', [DevCommandController::class, 'run'])->name('general.command.run');

        Route::get('cms/site', [SiteSettingsController::class, 'index'])->name('cms.site');
        Route::post('cms/site', [SiteSettingsController::class, 'index']);

        Route::get('cms/archives/export', [ArchivesController::class, 'export']);
        Route::get('cms/archives', [ArchivesController::class, 'index'])->name('cms.archives');
        Route::get('cms/archives/add', [ArchivesController::class, 'add']);
        Route::post('cms/archives/add', [ArchivesController::class, 'add']);
        Route::get('cms/archives/edit', [ArchivesController::class, 'edit']);
        Route::post('cms/archives/edit', [ArchivesController::class, 'edit']);
        Route::post('cms/archives/del', [ArchivesController::class, 'del']);
        Route::post('cms/archives/multi', [ArchivesController::class, 'multi']);
        Route::get('cms/archives/recyclebin', [ArchivesController::class, 'recyclebin']);
        Route::post('cms/archives/restore', [ArchivesController::class, 'restore']);
        Route::post('cms/archives/destroy', [ArchivesController::class, 'destroy']);
        Route::post('cms/archives/approve', [ArchivesController::class, 'approve']);
        Route::post('cms/archives/reject', [ArchivesController::class, 'reject']);
        Route::post('cms/archives/batch-special', [ArchivesController::class, 'batchSpecial']);
        Route::post('cms/archives/batch-tags', [ArchivesController::class, 'batchTags']);

        Route::get('cms/comment', [CommentController::class, 'index'])->name('cms.comment');
        Route::get('cms/comment/edit', [CommentController::class, 'edit']);
        Route::post('cms/comment/edit', [CommentController::class, 'edit']);
        Route::post('cms/comment/del', [CommentController::class, 'del']);
        Route::post('cms/comment/multi', [CommentController::class, 'multi']);
        Route::get('cms/comment/recyclebin', [CommentController::class, 'recyclebin']);
        Route::post('cms/comment/restore', [CommentController::class, 'restore']);
        Route::post('cms/comment/destroy', [CommentController::class, 'destroy']);

        Route::get('cms/special/export', [SpecialController::class, 'export']);
        Route::get('cms/special', [SpecialController::class, 'index'])->name('cms.special');
        Route::get('cms/special/add', [SpecialController::class, 'add']);
        Route::post('cms/special/add', [SpecialController::class, 'add']);
        Route::get('cms/special/edit', [SpecialController::class, 'edit']);
        Route::post('cms/special/edit', [SpecialController::class, 'edit']);
        Route::post('cms/special/del', [SpecialController::class, 'del']);
        Route::post('cms/special/multi', [SpecialController::class, 'multi']);
        Route::get('cms/special/recyclebin', [SpecialController::class, 'recyclebin']);
        Route::post('cms/special/restore', [SpecialController::class, 'restore']);
        Route::post('cms/special/destroy', [SpecialController::class, 'destroy']);
        Route::post('cms/special/batch-archives', [SpecialController::class, 'batchArchives']);

        Route::get('cms/block', [BlockController::class, 'index'])->name('cms.block');
        Route::get('cms/block/add', [BlockController::class, 'add']);
        Route::post('cms/block/add', [BlockController::class, 'add']);
        Route::get('cms/block/edit', [BlockController::class, 'edit']);
        Route::post('cms/block/edit', [BlockController::class, 'edit']);
        Route::post('cms/block/del', [BlockController::class, 'del']);
        Route::post('cms/block/multi', [BlockController::class, 'multi']);

        Route::get('cms/statistics', [StatisticsController::class, 'index'])->name('cms.statistics');

        Route::get('cms/model-designer', [ModelDesignerController::class, 'index'])->name('cms.model-designer');
        Route::get('cms/model-designer/add', [ModelDesignerController::class, 'add']);
        Route::post('cms/model-designer/add', [ModelDesignerController::class, 'add']);
        Route::get('cms/model-designer/edit', [ModelDesignerController::class, 'edit']);
        Route::post('cms/model-designer/edit', [ModelDesignerController::class, 'edit']);
        Route::post('cms/model-designer/del', [ModelDesignerController::class, 'del']);
        Route::post('cms/model-designer/multi', [ModelDesignerController::class, 'multi']);

        Route::get('cms/model-data', [ModelDataController::class, 'index'])->name('cms.model-data');
        Route::get('cms/model-data/add', [ModelDataController::class, 'add']);
        Route::post('cms/model-data/add', [ModelDataController::class, 'add']);
        Route::get('cms/model-data/edit', [ModelDataController::class, 'edit']);
        Route::post('cms/model-data/edit', [ModelDataController::class, 'edit']);
        Route::post('cms/model-data/del', [ModelDataController::class, 'del']);
        Route::post('cms/model-data/multi', [ModelDataController::class, 'multi']);
        Route::get('cms/model-data/recyclebin', [ModelDataController::class, 'recyclebin']);
        Route::post('cms/model-data/restore', [ModelDataController::class, 'restore']);
        Route::post('cms/model-data/destroy', [ModelDataController::class, 'destroy']);

        Route::get('cms/order', [CmsOrderController::class, 'index'])->name('cms.order');
        Route::get('cms/order/edit', [CmsOrderController::class, 'edit']);
        Route::post('cms/order/edit', [CmsOrderController::class, 'edit']);
        Route::get('cms/order/recyclebin', [CmsOrderController::class, 'recyclebin']);
        Route::post('cms/order/del', [CmsOrderController::class, 'del']);

        Route::get('cms/page/export', [CmsPageController::class, 'export']);
        Route::get('cms/page', [CmsPageController::class, 'index'])->name('cms.page');
        Route::get('cms/page/add', [CmsPageController::class, 'add']);
        Route::post('cms/page/add', [CmsPageController::class, 'add']);
        Route::get('cms/page/edit', [CmsPageController::class, 'edit']);
        Route::post('cms/page/edit', [CmsPageController::class, 'edit']);
        Route::post('cms/page/del', [CmsPageController::class, 'del']);
        Route::post('cms/page/multi', [CmsPageController::class, 'multi']);
        Route::get('cms/page/recyclebin', [CmsPageController::class, 'recyclebin']);
        Route::post('cms/page/restore', [CmsPageController::class, 'restore']);
        Route::post('cms/page/destroy', [CmsPageController::class, 'destroy']);

        Route::get('cms/tag', [CmsTagController::class, 'index'])->name('cms.tag');
        Route::get('cms/tag/add', [CmsTagController::class, 'add']);
        Route::post('cms/tag/add', [CmsTagController::class, 'add']);
        Route::get('cms/tag/edit', [CmsTagController::class, 'edit']);
        Route::post('cms/tag/edit', [CmsTagController::class, 'edit']);
        Route::post('cms/tag/del', [CmsTagController::class, 'del']);
        Route::post('cms/tag/multi', [CmsTagController::class, 'multi']);

        Route::get('general/apidoc', [ApidocController::class, 'index'])->name('general.apidoc');
        Route::get('general/sms', [SmsController::class, 'index'])->name('general.sms');
        Route::post('general/sms/send', [SmsController::class, 'send'])->name('general.sms.send');

        require __DIR__.'/admin_crud.php';
    });
});
