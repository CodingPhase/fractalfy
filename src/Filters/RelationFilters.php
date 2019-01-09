<?php

namespace CodingPhase\Fractalfy\Filters;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class RelationFilters
{
    /**
     * @var
     */
    protected $builder;

    /**
     * @var Collection
     */
    public $relations;

    /**
     * @var Collection
     */
    public $relationFilters;

    /**
     * @var Request
     */
    public $request;

    /**
     * @var array
     */
    public $requestedIncludes = [];

    /**
     * @var array
     */
    public $includeParams;

    /**
     * RelationFilters constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->relationFilters = collect();
        $this->relations = collect();
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Builder $builder, $classBasename)
    {
        $this->builder = $builder;

        foreach ($this->filters() as $name => $value) {
            $name = camel_case($name);

            $value = explode('|', $value);

            if (is_array($value) && count($value)) {
                $filterClassName = 'App\\Filters\\' . $classBasename . '\\'. ucfirst($name). 'Filter';
                if (class_exists($filterClassName)) {
                    call_user_func_array([$filterClassName, 'handle'], array_merge([$this->builder], $value));
                }
            }
        }

        return $this->builder;
    }

    /**
     * @return array
     */
    public function filters()
    {
        return $this->request->except('include');
    }

    /**
     * @return array
     */
    public function applyRelations(): array
    {
        $this->getfiltersFromRequests();
        $this->filterRelations();

        return $this->toArray();
    }

    /**
     * @return bool
     */
    public function filterRelations(): bool
    {
        if (is_array($this->includeParams)) {
            foreach ($this->includeParams as $key => $relationFilters) {
                $keyArray = explode('.',$key);
                $model = ucfirst(str_singular(end($keyArray)));
                $this->relations->put($key, function ($query) use ($relationFilters, $model) {
                    foreach ($relationFilters as $name => $value) {
                        $relationFiltersObject = app()->make('App\\Filters\\' . $model . '\\' . ucfirst($name) . 'Filter');

                        if (is_array($value) && count($value)) {
                            call_user_func_array([$relationFiltersObject, 'handle'], array_merge([$query], $value));
                        }
                    }
                });
            }

            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function defaultRelations(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public function getfiltersFromRequests()
    {
        $string = $this->request->get('include');
        if ($string != null) {
            $this->parseIncludes($string);
        }

        return $this->includeParams;

    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $relationsArray = array_merge($this->requestedIncludes, $this->defaultRelations());

        return array_merge($relationsArray, $this->relations->toArray());
    }

    /**
     * @param $includes
     * @return $this
     */
    public function parseIncludes($includes)
    {
        $this->requestedIncludes = $this->includeParams = [];

        if (is_string($includes)) {
            $includes = explode(',', $includes);
        }

        if (! is_array($includes)) {
            throw new \InvalidArgumentException(
                'The parseIncludes() method expects a string or an array. ' . gettype($includes) . ' given'
            );
        }

        foreach ($includes as $include) {
            list($includeName, $allModifiersStr) = array_pad(explode(':', $include, 2), 2, null);

            $includeName = $this->trimToAcceptableRecursionLevel($includeName);

            if (in_array($includeName, $this->requestedIncludes)) {
                continue;
            }
            $this->requestedIncludes[] = $includeName;

            // No Params? Bored
            if ($allModifiersStr === null) {
                continue;
            }

            // Matches multiple instances of 'something(foo|bar|baz)' in the string
            // I guess it ignores : so you could use anything, but probably don't do that
            preg_match_all('/([\w]+)(\(([^\)]+)\))?/', $allModifiersStr, $allModifiersArr);

            // [0] is full matched strings...
            $modifierCount = count($allModifiersArr[0]);

            $modifierArr = [];

            for ($modifierIt = 0; $modifierIt < $modifierCount; $modifierIt++) {
                // [1] is the modifier
                $modifierName = $allModifiersArr[1][$modifierIt];

                // and [3] is delimited params
                $modifierParamStr = $allModifiersArr[3][$modifierIt];

                // Make modifier array key with an array of params as the value
                $modifierArr[$modifierName] = explode('|', $modifierParamStr);
            }

            $this->includeParams[$includeName] = $modifierArr;
        }

        // This should be optional and public someday, but without it includes would never show up
        //$this->autoIncludeParents();

        return $this;
    }

    /**
     * Trim to Acceptable Recursion Level
     *
     * Strip off any requested resources that are too many levels deep, to avoid DiCaprio being chased
     * by trains or whatever the hell that movie was about.
     *
     * @internal
     *
     * @param string $includeName
     *
     * @return string
     */
    protected function trimToAcceptableRecursionLevel($includeName)
    {
        return implode('.', array_slice(explode('.', $includeName), 0, 3));
    }

    /**
     * Set Request Object
     *
     * @param $request
     * @return $this
     */
    public function setRequest($request)
    {
        $this->request = $request;

        return $this;
    }
}