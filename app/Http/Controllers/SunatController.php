<?php

namespace App\Http\Controllers;

use App\DTO\ClientDTO;
use App\DTO\CompanyDTO;
use App\DTO\InvoiceDTO;
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

        return response()->json($response);
    }

    function xml()
    {
        $invoice = new InvoiceDTO(
            serial: "F001-00000001",
            issu_date: "2021-01-01",
            issu_time: "12:00:00",
            document_type_code: "01",
            amount_letter: "CIENTO VEINTITRES CON 00/100 SOLES",
            currency_code: "PEN",
        );

        $company = new CompanyDTO(
            ruc: "20100000001",
            business_name: "EMPRESA SAC",
            trade_name: "EMPRESA",
            ubigeo: "150101",
            province: "LIMA",
            department: "LIMA",
            district: "LIMA",
            fiscal_address: "AV. LIMA 123",
        );

        $client = new ClientDTO(
            entity_type_code: "6",
            document_number: "20100000002",
            legal_name: "EMPRESA 2 SAC",
        );

        $items = [];

        return view("invoice", compact("invoice", "company", "client", "items"));
    }
}
