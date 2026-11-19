@extends('member.layouts.app')

@section('title', '投稿')

@section('content')
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card cms-member-card">
            <div class="card-header bg-white fw-semibold">提交投稿</div>
            <div class="card-body">
                <form method="post" action="{{ route('member.contribute.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small">栏目</label>
                        <select name="category_id" class="form-select" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">标题</label>
                        <input type="text" name="title" class="form-control" required maxlength="200">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">摘要</label>
                        <textarea name="description" class="form-control" rows="2" maxlength="500"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">正文</label>
                        <textarea name="content" class="form-control" rows="8" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">提交审核</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card cms-member-card">
            <div class="card-header bg-white fw-semibold">我的投稿</div>
            <ul class="list-group list-group-flush">
                @forelse($archives as $a)
                    <li class="list-group-item d-flex justify-content-between">
                        <span>{{ $a->title }}</span>
                        <span class="small text-muted">{{ $a->status }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted">暂无记录</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@endsection