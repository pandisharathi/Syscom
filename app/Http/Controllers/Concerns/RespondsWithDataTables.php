<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait RespondsWithDataTables
{
    protected function dataTablesJson(
        Request $request,
        Builder $query,
        array $searchableColumns,
        callable $rowFormatter,
        ?callable $searchCustomize = null,
    ): JsonResponse {
        $total = (clone $query)->count();

        if ($search = $request->input('search.value')) {
            $query->where(function (Builder $w) use ($search, $searchableColumns, $searchCustomize) {
                if ($searchCustomize) {
                    $searchCustomize($w, $search);

                    return;
                }
                foreach ($searchableColumns as $col) {
                    $w->orWhere($col, 'like', '%'.$search.'%');
                }
            });
        }

        $filtered = (clone $query)->count();

        $columnIndex = $request->input('order.0.column');
        $columnName = $request->input("columns.$columnIndex.name");
        $dir = $request->input('order.0.dir', 'desc');
        if ($columnName && in_array($dir, ['asc', 'desc'], true)) {
            $query->orderBy($columnName, $dir);
        }

        $start = max(0, (int) $request->input('start', 0));
        $length = min(100, max(1, (int) $request->input('length', 10)));

        $rows = $query->skip($start)->take($length)->get();

        return response()->json([
            'draw' => (int) $request->input('draw', 1),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $rows->map(fn ($m) => $rowFormatter($m))->values()->all(),
        ]);
    }
}
