<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BootstrapTable
{
    /**
     * @param  array<int, string>  $searchable
     * @param  array<int, string>  $sortable
     * @return array{total: int, rows: array<int, mixed>}
     */
    public function fetch(Builder $query, Request $request, array $searchable = [], array $sortable = [], array $filterable = []): array
    {
        $this->applyQuickSearch($query, $request, $searchable);
        $allowedFilters = $filterable !== [] ? $filterable : array_values(array_unique(array_merge($searchable, $sortable)));
        $this->applyFilter($query, $request, $allowedFilters);
        $this->applySort($query, $request, $sortable);

        $total = (clone $query)->count();

        $offset = max(0, (int) $request->input('offset', 0));
        $limit = max(1, min(500, (int) $request->input('limit', 10)));

        if ($offset === 0 && $request->has('page') && ! $request->has('offset')) {
            $page = max(1, (int) $request->input('page', 1));
            $offset = ($page - 1) * $limit;
        }

        $rows = $query->skip($offset)->take($limit)->get();

        return [
            'total' => $total,
            'rows' => $rows->all(),
        ];
    }

    /**
     * @param  array<int, string>  $searchable
     */
    protected function applyQuickSearch(Builder $query, Request $request, array $searchable): void
    {
        $search = trim((string) $request->input('search', ''));
        if ($search === '' || $searchable === []) {
            return;
        }

        $query->where(function (Builder $q) use ($search, $searchable) {
            foreach ($searchable as $field) {
                $q->orWhere($field, 'like', '%'.$search.'%');
            }
        });
    }

    /**
     * @param  array<int, string>  $allowedFields
     */
    protected function applyFilter(Builder $query, Request $request, array $allowedFields = []): void
    {
        $filterRaw = $request->input('filter', '{}');
        $opRaw = $request->input('op', '{}');

        if (is_array($filterRaw)) {
            $filter = $filterRaw;
        } else {
            $filter = json_decode((string) $filterRaw, true) ?: [];
        }

        if (is_array($opRaw)) {
            $op = $opRaw;
        } else {
            $op = json_decode((string) $opRaw, true) ?: [];
        }

        foreach ($filter as $field => $value) {
            if ($allowedFields !== [] && ! in_array($field, $allowedFields, true)) {
                continue;
            }
            if ($value === '' || $value === null) {
                continue;
            }
            $operator = strtoupper((string) ($op[$field] ?? '='));
            $parts = is_array($value) ? $value : explode(',', (string) $value);
            match ($operator) {
                'LIKE' => $query->where($field, 'like', '%'.$value.'%'),
                'IN' => $query->whereIn($field, $parts),
                'BETWEEN', 'RANGE' => $query->whereBetween($field, [
                    $parts[0] ?? 0,
                    $parts[1] ?? $parts[0] ?? 0,
                ]),
                '>' => $query->where($field, '>', $value),
                '<' => $query->where($field, '<', $value),
                '>=' => $query->where($field, '>=', $value),
                '<=' => $query->where($field, '<=', $value),
                default => $query->where($field, $value),
            };
        }
    }

    /**
     * @param  array<int, string>  $sortable
     */
    protected function applySort(Builder $query, Request $request, array $sortable): void
    {
        $sort = (string) $request->input('sort', 'id');
        $order = strtolower((string) $request->input('order', 'desc')) === 'asc' ? 'asc' : 'desc';
        if ($sortable !== [] && ! in_array($sort, $sortable, true)) {
            $sort = 'id';
        }
        $query->orderBy($sort, $order);
    }
}
