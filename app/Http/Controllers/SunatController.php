<?php

namespace App\Http\Controllers;

use App\Services\XMLService;
use Illuminate\Http\Request;

class SunatController extends Controller
{
    protected $xmlService;

    function __construct(XMLService $xmlService)
    {
        $this->xmlService = $xmlService;
    }

    function send(Request $request)
    {

        $data = $request->all();
        $company = $data["company"];
        $client = $data["client"];
        $sale = $data["sale"];
        $items = $data["items"];

        $response = $this->xmlService->generate($company, $client, $sale, $items);

        return response($response);
    }
}
