<?php

namespace App\Http\Filters;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class ContactSearchFilter extends ContactFilters
{
    protected $filters = ['search'];

    protected function search($value)
    {
        $this->builder->where(function($query) use ($value) {
            $query->where('name', 'like', "%{$value}%")
                  ->orWhere('email', 'like', "%{$value}%")
                  ->orWhere('phone', 'like', "%{$value}%");
        });
    }
} 