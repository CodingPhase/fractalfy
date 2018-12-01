<?php

namespace CodingPhase\Fractalfy\Traits;

use Illuminate\Http\Request;

/**
 * Class InteractsWithFractal
 * @package CodingPhase\Fractalfy\Traits\Fractal
 */
trait InteractsWithFractal
{
    /**
     * @return mixed
     */
    abstract function map();

    /**
     * @param $filters
     *
     * @return array
     */
    public function transform($filters)
    {
        $data = [];

        $map = $this->map();

        foreach($filters as $name => $filter) {
            $mapItem = $map[$name];

            foreach ($filter as $key => $value) {
                $data[$mapItem[$key]] = $value;
            }
        }

        return $data;
    }

    /**
     * @return array
     */
    public function filters()
    {
        if($this->request instanceof Request) {
            return parent::filters();
        }

        return $this->transform($this->request);
    }
}