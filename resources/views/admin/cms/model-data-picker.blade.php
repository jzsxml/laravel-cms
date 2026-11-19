@extends('admin.layouts.app')

@section('title', '模型数据')

@section('content')
<h1 class="h5 mb-3">选择模型</h1>
<ul class="list-group">
    @forelse($models as $m)
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <span>{{ $m->title }} <code class="small">{{ $m->name }}</code></span>
            <a href="{{ url(config('cms.admin_path').'/cms/model-data?model_id='.$m->id) }}" class="btn btn-sm btn-primary">管理数据</a>
        </li>
    @empty
        <li class="list-group-item text-muted">请先在「模型设计」中创建模型</li>
    @endforelse
</ul>
@endsection