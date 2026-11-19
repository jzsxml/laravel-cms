<?php

namespace App\Http\Controllers\Admin\General;

use App\Http\Controllers\Admin\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class ApidocController extends Controller
{
    public function index(Request $request): View
    {
        $rows = [];
        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();
            if (! str_starts_with($uri, 'api/') && $uri !== 'api') {
                continue;
            }
            $methods = array_filter($route->methods(), fn ($m) => $m !== 'HEAD');
            $rows[] = [
                'methods' => implode('|', $methods),
                'uri' => '/'.$uri,
                'name' => $route->getName(),
                'action' => $route->getActionName(),
            ];
        }
        usort($rows, fn ($a, $b) => strcmp($a['uri'], $b['uri']));

        return view('admin.general.apidoc.index', [
            'title' => 'API 文档',
            'routes' => $rows,
            'tokenHeader' => config('cms.api.token_header', 'HTTP_X_CMS_TOKEN'),
        ]);
    }
}
