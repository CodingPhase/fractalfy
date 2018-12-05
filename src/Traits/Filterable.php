<?php

namespace CodingPhase\Fractalfy\Traits;

use CodingPhase\Fractalfy\Filters\RelationFilters;
use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    /**
     * Filter results
     *
     * @param Builder $builder
     * @param RelationFilters $filters
     * @return Builder
     */
    public function scopeFilter(Builder $builder, RelationFilters $filters)
    {
        return $filters->apply($builder);
    }

    /**
     * @param RelationFilters $filters
     * @return mixed
     */
    public function relationFilter(RelationFilters $filters)
    {
        $this->load($filters->applyRelations());

        return $this;
    }
}
