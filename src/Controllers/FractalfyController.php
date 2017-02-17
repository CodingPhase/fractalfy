<?php

namespace Hesto\Fractalfy\Controllers;

use Hesto\Fractalfy\Fractalfy;
use Hesto\Fractalfy\Traits\Helpers;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FractalfyController extends Controller
{
    use Helpers, AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $fractal;

    public function __construct(Fractalfy $fractal, Request $request)
    {
        $this->fractal = $fractal;

        if ($request->has('include')) {
            $this->fractal->parseIncludes($request->get('include'));
        }
    }
}
