<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Admin\CmsModel;
use App\Models\Admin\CmsModelData;
use App\Services\Cms\ModelSchema;
use Illuminate\View\View;

class ModelDataController extends Controller
{
    public function __construct(
        protected PortalController $portal,
    ) {}

    public function index(string $name): View
    {
        $model = CmsModel::query()
            ->where('name', $name)
            ->where('status', 'normal')
            ->firstOrFail();

        $rows = CmsModelData::query()
            ->where('model_id', $model->id)
            ->where('status', 'normal')
            ->whereNull('deletetime')
            ->orderByDesc('weigh')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $schema = app(ModelSchema::class);
        $items = $rows->map(fn (CmsModelData $row) => [
            'id' => $row->id,
            'title' => $schema->listTitle($model, $row),
            'data' => $row->dataArray(),
        ]);

        return view('portal.model', array_merge(
            $this->portal->sharedPortalContext(),
            ['model' => $model, 'items' => $items]
        ));
    }
}
