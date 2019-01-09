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
     * @return Builder
     */
    public function scopeFilter(Builder $builder)
    {
        $builder = $builder->with(app()->make(RelationFilters::class)->applyRelations());

        return app()->make(RelationFilters::class)->apply($builder, class_basename($this));
    }

    /**
     * Filter results
     *
     * @param Builder $builder
     * @return Builder
     */
    public function scopewithFilteredRelations(Builder $builder)
    {
        return $builder->with(app()->make(RelationFilters::class)->applyRelations());
    }

    /**
     * @return mixed
     */
    public function filteredRelations()
    {
        $this->load(app()->make(RelationFilters::class)->applyRelations());

        return $this;
    }
}
