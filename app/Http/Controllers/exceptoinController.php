<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\ApiError;

class exceptoinController extends Controller
{
    public function index($strError){
    	$objError = ApiError::where('name', $strError)->select('title', 'error_code', 'description')->active()->first();

    	return $this->httpResponse($objError->only(['title', 'description']), $objError->error_code);
    }
}
