@extends('admin.layouts.app')

@section('title', $title)

@section('content')
<div class="alert alert-info py-2 small border-0 mb-3">
    此处配置与前台门户、页脚展示一致，保存后写入系统配置表并自动刷新配置缓存。
</div>
<div class="card cms-card">
    <div class="card-body p-4" style="max-width:640px">
        <form method="post" action="{{ route('admin.cms.site') }}" id="site-settings-form">
            @csrf
            @foreach($definitions as $name => $def)
                <div class="mb-3">
                    <label class="form-label" for="field-{{ $name }}">
                        {{ $def['title'] }}
                        @if($name === 'sitename')<span class="text-danger">*</span>@endif
                    </label>
                    @if(($def['type'] ?? '') === 'select' && ! empty($def['options']))
                        <select name="{{ $name }}" id="field-{{ $name }}" class="form-select">
                            @foreach($def['options'] as $val => $label)
                                <option value="{{ $val }}" @selected(old($name, $values[$name] ?? '') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    @else
                        <input type="text" name="{{ $name }}" id="field-{{ $name }}" class="form-control"
                               value="{{ old($name, $values[$name] ?? '') }}"
                               @if($name === 'sitename') required maxlength="100" @else maxlength="500" @endif>
                    @endif
                    @if(!empty($def['hint']))
                        <div class="form-text">{{ $def['hint'] }}</div>
                    @endif
                    @error($name)
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
            @endforeach
            <button type="submit" class="btn btn-primary">保存</button>
            <a href="{{ url('/') }}" target="_blank" class="btn btn-outline-secondary ms-2">预览前台</a>
        </form>
    </div>
</div>
@endsection