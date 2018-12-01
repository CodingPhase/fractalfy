<?php

namespace CodingPhase\Fractalfy\Traits;

use CodingPhase\Fractalfy\Filters\QueryFilters;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class Filterable
 * @package CodingPhase\Fractalfy\Traits\Eloquent
 */
trait Filterable
{
    /**
     * Filter results
     *
     * @param Builder $builder
     * @param QueryFilters $filters
     * @return Builder
     */
    public function scopeFilter(Builder $builder, QueryFilters $filters)
    {
        return $filters->apply($builder);
    }
}
