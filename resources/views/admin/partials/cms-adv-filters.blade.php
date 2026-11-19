@if(!empty($filterFields))
<div class="cms-adv-filter collapse {{ !empty($filterOpen) ? 'show' : '' }}" id="cms-adv-filter">
    <div class="cms-adv-filter-inner">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-semibold small text-secondary"><i class="fas fa-filter me-1"></i> {{ __('admin.ui.adv_filter') }}</span>
            <button type="button" class="btn btn-link btn-sm text-muted p-0" data-bs-toggle="collapse" data-bs-target="#cms-adv-filter" aria-label="{{ __('admin.ui.collapse') }}">{{ __('admin.ui.collapse') }}</button>
        </div>
        <div class="row g-2 align-items-end">
            @foreach($filterFields as $field => $meta)
                @php
                    $type = $meta['type'] ?? 'text';
                    $defaultOp = $meta['defaultOp'] ?? 'LIKE';
                    $ops = $meta['ops'] ?? match ($type) {
                        'number' => ['=', '>=', '<='],
                        'select' => ['='],
                        'daterange' => ['RANGE'],
                        default => ['LIKE', '='],
                    };
                @endphp
                <div class="col-6 col-md-4 col-lg-3 cms-filter-item" data-field="{{ $field }}" data-default-op="{{ $defaultOp }}">
                    <label class="form-label small mb-1">{{ $meta['label'] ?? $field }}</label>
                    <div class="input-group input-group-sm flex-wrap">
                        @if(count($ops) > 1)
                            <select class="form-select cms-filter-op" style="max-width:72px">
                                @foreach($ops as $op)
                                    <option value="{{ $op }}" @selected($op === $defaultOp)>{{ $op }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="hidden" class="cms-filter-op" value="{{ $defaultOp }}">
                        @endif
                        @if($type === 'select')
                            <select class="form-select cms-filter-val flex-grow-1">
                                <option value="">{{ __('admin.ui.all') }}</option>
                                @foreach($meta['options'] ?? [] as $k => $lab)
                                    <option value="{{ $k }}">{{ $lab }}</option>
                                @endforeach
                            </select>
                        @elseif($type === 'daterange')
                            <span class="d-flex gap-1 flex-grow-1 w-100 mt-1 mt-md-0">
                                <input type="date" class="form-control cms-filter-val-start" title="开始日期">
                                <input type="date" class="form-control cms-filter-val-end" title="结束日期">
                            </span>
                        @elseif($type === 'number')
                            <input type="number" class="form-control cms-filter-val flex-grow-1" placeholder="数值">
                        @else
                            <input type="text" class="form-control cms-filter-val flex-grow-1" placeholder="关键词">
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="d-flex flex-wrap gap-2 mt-3">
            <button type="button" class="btn btn-sm btn-primary" id="btn-apply-filter"><i class="fas fa-check me-1"></i>应用筛选</button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-reset-filter"><i class="fas fa-undo me-1"></i>重置</button>
        </div>
        <div class="cms-filter-tags d-flex flex-wrap gap-1 mt-2" id="cms-filter-tags"></div>
    </div>
</div>
@endif