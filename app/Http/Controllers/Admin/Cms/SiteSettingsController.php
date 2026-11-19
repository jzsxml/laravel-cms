<?php

namespace App\Http\Controllers\Admin\Cms;

use App\Http\Controllers\Admin\Controller;
use App\Support\SiteSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteSettingsController extends Controller
{
    public function index(Request $request): View|JsonResponse|RedirectResponse
    {
        if ($request->isMethod('post')) {
            $data = $request->validate(SiteSettings::validationRules());
            SiteSettings::save($data);
            $this->adminAuth->recordLog($request, '站点配置', '保存站点信息');

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['code' => 1, 'msg' => '保存成功，前台刷新后即可看到更新']);
            }

            return redirect()
                ->route('admin.cms.site')
                ->with('success', '保存成功');
        }

        return view('admin.cms.site-settings.index', [
            'title' => '站点配置',
            'definitions' => SiteSettings::definitions(),
            'values' => SiteSettings::values(),
        ]);
    }
}
