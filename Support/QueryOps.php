<?php
namespace QuikAPI\Support;

use Illuminate\Database\Eloquent\Builder;

class QueryOps
{
    /**
     * Apply structured operations to an Eloquent query builder.
     * Each operation: ['code' => 'where', 'parameters' => [...]]
     */
    public static function addOperationsInQuery(Builder $query, array $operations): Builder
    {
        foreach ($operations as $operation) {
            $code = $operation['code'] ?? null;
            $p = $operation['parameters'] ?? [];
            switch ($code) {
                case 'where':
                    $query->where($p['column'] ?? null, $p['operator'] ?? '=', $p['value'] ?? null);
                    break;
                case 'or_where':
                    $query->orWhere($p['column'] ?? null, $p['operator'] ?? '=', $p['value'] ?? null);
                    break;
                case 'where_has':
                    $rel = $operation['relation'] ?? null;
                    $query->whereHas($rel, function ($q) use ($p) {
                        return $q->where($p['column'] ?? null, $p['operator'] ?? '=', $p['value'] ?? null);
                    });
                    break;
                case 'has':
                    $query->has($operation['relation'] ?? '');
                    break;
                case 'where_between':
                    $query->whereBetween($p['column'] ?? '', $p['values'] ?? []);
                    break;
                case 'or_where_between':
                    $query->orWhereBetween($p['column'] ?? '', $p['values'] ?? []);
                    break;
                case 'where_not_between':
                    $query->whereNotBetween($p['column'] ?? '', $p['values'] ?? []);
                    break;
                case 'or_where_not_between':
                    $query->orWhereNotBetween($p['column'] ?? '', $p['values'] ?? []);
                    break;
                case 'where_in':
                    $query->whereIn($p['column'] ?? '', $p['values'] ?? []);
                    break;
                case 'where_not_in':
                    $query->whereNotIn($p['column'] ?? '', $p['values'] ?? []);
                    break;
                case 'or_where_in':
                    $query->orWhereIn($p['column'] ?? '', $p['values'] ?? []);
                    break;
                case 'or_where_not_in':
                    $query->orWhereNotIn($p['column'] ?? '', $p['values'] ?? []);
                    break;
                case 'where_null':
                    $query->whereNull($p['column'] ?? '');
                    break;
                case 'where_not_null':
                    $query->whereNotNull($p['column'] ?? '');
                    break;
                case 'or_where_null':
                    $query->orWhereNull($p['column'] ?? '');
                    break;
                case 'or_where_not_null':
                    $query->orWhereNotNull($p['column'] ?? '');
                    break;
                case 'where_date':
                    $query->whereDate($p['column'] ?? '', $p['operator'] ?? '=', $p['value'] ?? null);
                    break;
                case 'where_month':
                    $query->whereMonth($p['column'] ?? '', $p['operator'] ?? '=', $p['value'] ?? null);
                    break;
                case 'where_day':
                    $query->whereDay($p['column'] ?? '', $p['operator'] ?? '=', $p['value'] ?? null);
                    break;
                case 'where_year':
                    $query->whereYear($p['column'] ?? '', $p['operator'] ?? '=', $p['value'] ?? null);
                    break;
                case 'where_time':
                    $query->whereTime($p['column'] ?? '', $p['operator'] ?? '=', $p['value'] ?? null);
                    break;
                case 'where_column':
                    $query->whereColumn($p['column_1'] ?? '', $p['operator'] ?? '=', $p['column_2'] ?? '');
                    break;
                case 'or_where_column':
                    $query->orWhereColumn($p['column_1'] ?? '', $p['operator'] ?? '=', $p['column_2'] ?? '');
                    break;
                case 'having':
                    $query->having($p['column'] ?? '', $p['operator'] ?? '=', $p['value'] ?? null);
                    break;
                default:
                    // ignore unknown ops
                    break;
            }
        }
        return $query;
    }
}
