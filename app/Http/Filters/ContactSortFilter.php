<?php

namespace App\Http\Filters;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class ContactSortFilter extends ContactFilters
{
    protected $filters = ['sort_by', 'sort_direction'];

    protected function search($value)
    {
        // Not used in this filter
    }

    protected function sort_by($value)
    {
        $allowedSorts = ['name', 'email', 'phone', 'created_at', 'updated_at'];
        
        if (in_array($value, $allowedSorts)) {
            $direction = $this->request->get('sort_direction', 'asc');
            $this->builder->orderBy($value, $direction);
        } else {
            $this->builder->latest();
        }
    }

    protected function sort_direction($value)
    {
        // This is handled in sort_by method
    }
} 