@foreach($nodes as $node)
    <div class="rule-node" style="margin-left: {{ ($depth ?? 0) * 1.25 }}rem">
        <label class="d-flex align-items-center gap-2 mb-1 small">
            <input type="checkbox"
                   class="form-check-input rule-check m-0"
                   name="rule_ids[]"
                   value="{{ $node['id'] }}"
                   data-pid="{{ $node['pid'] }}"
                   @checked(in_array($node['id'], $selected ?? [], true))>
            <span>{{ $node['title'] }} <span class="text-muted">({{ $node['name'] }})</span></span>
        </label>
    </div>
    @if(!empty($node['children']))
        @include('admin.auth.group.partials.rule-node', [
            'nodes' => $node['children'],
            'depth' => ($depth ?? 0) + 1,
            'selected' => $selected,
        ])
    @endif
@endforeach