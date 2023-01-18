<?php

namespace App\Traits;

use App\Models\AccountType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait SearchableTrait
{

    public function search($model, $relations = null)
    {
        $request = request();
        $sort = $request->descending === 'true' ? 'desc' : 'asc';
        $headings = collect($request->headings)->pluck('name')->toArray();
        return $model::when($request->filter, function ($query, $filter) use ($headings) {
            $query->where(function ($query) use ($filter, $headings) {
                foreach ($headings as $heading) {
                    $parts = explode('.', $heading);
                    if (count($parts) > 1) {
                        $column = array_pop($parts);
                        $relation = implode(".", $parts);
                        if ($column == 'full_name') {
                            $query->orWhereRelation($relation, fn ($q) => $q->whereFullName($filter));
                        } else {
                            $query->orWhereRelation($relation, $column, 'LIKE', '%' . $filter . '%');
                        }
                    } else {
                        if ($heading == 'full_name') {
                            $query->orWhere(fn ($q) => $q->whereFullName($filter));
                        } else {
                            $query->orWhere($heading, 'LIKE', '%' . $filter . '%');
                        }
                    }
                }
            });
        })
            ->when($request->search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $mode = "orWhere";
                    $partsMode = "orWhereRelation";

                    foreach ($search as $searchKey => $searchVal) {

                        if ($searchKey === 'mode') {
                            $mode = $searchVal === 'and' ? 'where' : 'orWhere';
                            $partsMode = $searchVal === 'and' ? 'whereRelation' : 'orWhereRelation';
                            continue;
                        }

                        if (empty($searchVal)) continue;

                        if (!is_array($searchVal)) {

                            $parts = explode('.', $searchKey);
                            if (count($parts) > 1) {
                                $column = array_pop($parts);
                                $relation = implode(".", $parts);
                                if ($column == 'full_name') {

                                    $query->$partsMode($relation, fn ($q) => $q->whereFullName($searchVal));
                                } else {
                                    $query->$partsMode($relation, $column, 'LIKE', '%' . $searchVal . '%');
                                }
                            } else {
                                if ($searchKey == 'full_name') {
                                    $query->$mode(fn ($q) => $q->whereFullName($searchVal));
                                } else {
                                    $query->$mode($searchKey, 'LIKE', '%' . $searchVal . '%');
                                }
                            }

                            continue;
                        }

                        if ($searchVal['type'] === 'date') {

                            $dateMode = $searchVal['mode'] === 'and' ? 'whereDate' : 'orWhereDate';

                            $parts = explode('.', $searchKey);
                            if (count($parts) > 1) {
                                $column = array_pop($parts);
                                $relation = implode(".", $parts);
                                $query->$partsMode($relation, function ($query) use ($dateMode, $column, $searchVal) {

                                    foreach ($searchVal as $date) {

                                        if (!is_array($date) || !$date['value']) continue;

                                        $query->$dateMode($column, $date['symbol'], $date['value']);
                                    }
                                });
                            } else {
                                $query->$mode(function ($query) use ($dateMode, $searchKey, $searchVal) {

                                    foreach ($searchVal as $date) {

                                        if (!is_array($date) || !$date['value']) continue;

                                        $query->$dateMode($searchKey, $date['symbol'], $date['value']);
                                    }
                                });
                            }
                        }
                    }
                });
            })->when($request->sortBy, function ($query, $sortBy) use ($sort) {
                $parts = explode('.', $sortBy);

                if (count($parts) > 1) {
                    $column = array_pop($parts);
                    $relation = implode(".", $parts);
                    if ($column == 'full_name') {
                        $query->orderByLeftPowerJoins([$relation, DB::raw("REPLACE(CONCAT(COALESCE(users.first_name,''),' ',COALESCE(users.middle_name,''),' ',COALESCE(users.last_name,'')),'  ',' ')")], $sort);
                    } else {
                        $query->orderByLeftPowerJoins($sortBy, $sort);
                    }
                } else {
                    if ($sortBy == 'full_name') {
                        $query->orderBy(DB::raw("REPLACE(CONCAT(COALESCE(first_name,''),' ',COALESCE(middle_name,''),' ',COALESCE(last_name,'')),'  ',' ')"), $sort);
                    } else {
                        $query->orderBy($sortBy, $sort);
                    }
                }
            })
            ->when($relations, function ($query, $relations) {
                $query->with($relations);
            });
    }
}
