<?php

namespace App\DTO;

class InvoiceDTO
{
    public function __construct(
        public string $serial,
        public string $issu_date,
        public string $issu_time,
        public string $document_type_code,
        public string $amount_letter,
        public string $currency_code,
        public string $payment_method_id = "01",
        public float $global_discount_percentage = 0.00,
        public float $total_igv = 0.00,
        public float $total_taxable = 0.00,
        public float $total_exonerated = 0.00,
        public float $total_unaffected = 0.00,
    ) {
    }
}

class DetailDTO
{
    public function __construct(
        public string $currency_code,
        public string $quantity
    ) {
    }
}

class CompanyDTO
{
    public function __construct(
        public string $ruc,
        public string $business_name,
        public string $trade_name,
        public string $ubigeo,
        public string $province,
        public string $department,
        public string $district,
        public string $fiscal_address,
    ) {
    }
}

class ClientDTO
{
    public function __construct(
        public string $entity_type_code,
        public string $document_number,
        public string $legal_name,
    ) {
    }
}
