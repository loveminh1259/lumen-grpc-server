<?php

namespace App\Scopes;

use Illuminate\Support\Facades\DB;

trait CustomScope {
    public function scopeDynamicLike($query, $column, $value, $mode = '') {
        if ($value == null || $value == '') return $query;
        return $query->where(function ($q) use ($column, $value, $mode) {
            $q->whereRaw("lower($column) like $mode '$value'")
                ->orWhereRaw("lower($column) like $mode '$value%'")
                ->orWhereRaw("lower($column) like $mode '%$value%'")
                ->orWhereRaw("lower($column) like $mode '%$value'");
        });
    }
    public function scopeOrDynamicLike($query, $column, $value, $mode = '') {
        if ($value == null || $value == '') return $query;
        return $query->orWhere(function ($q) use ($column, $value, $mode) {
            $q->whereRaw("lower($column) like $mode '$value'")
                ->orWhereRaw("lower($column) like $mode '$value%'")
                ->orWhereRaw("lower($column) like $mode '%$value%'")
                ->orWhereRaw("lower($column) like $mode '%$value'");
        });
    }
    public function scopeFindArrayInSet($query, $field, $values) {
        if ($values == null || count($values) == 0) return $query;
        $query->where(function ($q) use ($field, $values) {
            foreach ($values as $value) {
                $q->orFindInSet($field, $value, true);
            }
        });
        return $query;
    }
    public function scopeFindInSet($query, $field, $value, $reverse = false) {
        if ($value == null || $value == '') return $query;
        if ($reverse) {
            return $query->whereRaw("FIND_IN_SET('$value', $field) != 0");
        }
        return $query->whereRaw("FIND_IN_SET($field, '$value') != 0");
    }
    public function scopeOrFindInSet($query, $field, $value, $reverse = false) {
        if ($value == null || $value == '') return $query;
        if ($reverse) {
            return $query->orWhereRaw("FIND_IN_SET('$value', $field) != 0");
        }
        return $query->orWhereRaw("FIND_IN_SET($field, '$value') != 0");
    }
    public function scopeInRange($query, string $field, float $min, float $max) {

        return $query->whereRaw("$field between CAST($min as int) and CAST($max as int)");
    }
    public function scopeWhereExcludeNull($query, $column, $value) {
        if ($value != null) {
            return $query->where($column, $value);
        }
        return $query;
    }
    public function scopeWhereInExcludeEmpty($query, $column, array $values) {
        if (count($values) > 0) {
            return $query->whereIn($column, $values);
        }
        return $query;
    }
    public function scopeWhereExcludeEmpty($query, $column, $value) {
        if ($value != null) $query->where($column, $value);
        return $query;
    }
    public function scopeOrderByField($query, $field, array $values, $mode = 'asc') {
        $sql = "$field";
        foreach ($values as $value) {
            $sql = "$sql,'$value'";
        }
        $sql = "FIELD($sql)";
        return $query->orderBy(DB::raw("$sql"), $mode);
    }

    public function scopeWhereRelationIn($query, $relation, $column, array $array) {
        return $query->whereHas($relation, function($q) use ($column, $array) {
            return $q->whereIn("$column", $array);
        });
    }
}
