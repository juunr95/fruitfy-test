<?php

namespace App\Http\Filters;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pipeline\Pipeline;

class ContactPipeline
{
    protected $request;
    protected $filters = [
        ContactSearchFilter::class,
        ContactSortFilter::class,
    ];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply(Builder $builder): Builder
    {
        return app(Pipeline::class)
            ->send($builder)
            ->through($this->getFilters())
            ->thenReturn();
    }

    protected function getFilters(): array
    {
        return array_map(function ($filter) {
            return new $filter($this->request);
        }, $this->filters);
    }
} 