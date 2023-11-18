<?php

namespace App\Services;

use App\Helpers\Numletras;
use App\Helpers\Variables;

class XMLService
{
    public function some()
    {
        return "Generando xml...";
    }

    public function generate($company, $client, $sale, $items, $payments = [], $attached_guides = [])
    {
        $variables = new Variables();

        $total_igv          = ($sale['total_igv'] != null)         ? $sale['total_igv'] : 0.0;
        $total_taxable      = ($sale['total_taxable'] == null)     ? 0 : $sale['total_taxable'];
        $total_exonerated    = ($sale['total_exonerated'] == null)   ? 0 : $sale['total_exonerated'];
        $total_unaffected     = ($sale['total_unaffected'] == null)    ? 0 : $sale['total_unaffected'];
        $total_pay      = number_format(($total_taxable + $total_exonerated + $total_unaffected + $total_igv), 2, '.', '');

        $currency_arr = $this->moneda($sale['currency_id']);
        $currency_code = $currency_arr[0];
        $currency_description = $currency_arr[1];

        $num = new Numletras();
        $total_sale = explode(".", $total_pay);
        $total_letter = $num->num2letras($total_sale[0]);
        $sale['total_letters'] = $total_letter . ' con ' . $total_sale[1] . '/100 ' . $currency_description;

        $ini_line   = '';
        $fin_line   = '';
        $total_pay_tag = '';
        $credit_note_data = '';
        $line = '';
        $quantity = '';

        switch ($sale['document_type_code']) {
            case '01': // Factura
                $ini_line   = '<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ccts="urn:un:unece:uncefact:documentation:2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2" xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
                $fin_line   = 'Invoice';
                $invoice_type_code = '<cbc:InvoiceTypeCode listID="0101" listAgencyName="PE:SUNAT" listName="Tipo de Documento" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01" name="Tipo de Operacion" listSchemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo51">' . $sale['document_type_code'] . '</cbc:InvoiceTypeCode>';
                $total_pay_tag = 'LegalMonetaryTotal';
                $line      = 'InvoiceLine';
                $quantity   = 'InvoicedQuantity';
                break;

            case '03': // Boleta
                $ini_line   = '<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ccts="urn:un:unece:uncefact:documentation:2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2" xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
                $fin_line   = 'Invoice';
                $invoice_type_code = '<cbc:InvoiceTypeCode listID="0101" listAgencyName="PE:SUNAT" listName="Tipo de Documento" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01" name="Tipo de Operacion" listSchemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo51">' . $sale['document_type_code'] . '</cbc:InvoiceTypeCode>';
                $total_pay_tag = 'LegalMonetaryTotal';
                $line      = 'InvoiceLine';
                $quantity   = 'InvoicedQuantity';
                break;

            case '07': // Nota de Credito
                $ini_line   = '<CreditNote xmlns="urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">';
                $fin_line   = 'CreditNote';
                $invoice_type_code = '';

                $credit_note_data = '
            <cac:DiscrepancyResponse>
                <cbc:ReferenceID>' . $sale['related_serial'] . '-' . $sale['related_number'] . '</cbc:ReferenceID>
                <cbc:ResponseCode>' . $sale['related_motive_code'] . '</cbc:ResponseCode>
                <cbc:Description>' . $variables->credit_note_type($sale['related_motive_code']) . '</cbc:Description>
            </cac:DiscrepancyResponse>
            <cac:BillingReference>
                <cac:InvoiceDocumentReference>
                    <cbc:ID>' . $sale['related_serial'] . '-' . $sale['related_number'] . '</cbc:ID>
                    <cbc:DocumentTypeCode>' . $sale['related_document_type'] . '</cbc:DocumentTypeCode>
                </cac:InvoiceDocumentReference>
            </cac:BillingReference>';
                $total_pay_tag = 'LegalMonetaryTotal';

                $line      = 'CreditNoteLine';
                $quantity   = 'CreditedQuantity';
                break;

            case '08': // Nota de Debito
                $ini_line   = '<DebitNote xmlns="urn:oasis:names:specification:ubl:schema:xsd:DebitNote-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ccts="urn:un:unece:uncefact:documentation:2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2" xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">';
                $fin_line   = 'DebitNote';
                $invoice_type_code = '';
                $credit_note_data = '
            <cac:DiscrepancyResponse>
                <cbc:ReferenceID>' . $sale['related_serial'] . '-' . $sale['related_number'] . '</cbc:ReferenceID>
                <cbc:ResponseCode>' .  $sale['related_motive_code'] . '</cbc:ResponseCode>
                <cbc:Description>' . $variables->debit_note_type($sale['related_motive_code'])  . '</cbc:Description>
            </cac:DiscrepancyResponse>
            <cac:BillingReference>
                <cac:InvoiceDocumentReference>
                    <cbc:ID>' . $sale['related_serial'] . '-' . $sale['related_number'] . '</cbc:ID>
                    <cbc:DocumentTypeCode>' . $sale['related_document_type'] . '</cbc:DocumentTypeCode>
                </cac:InvoiceDocumentReference>
            </cac:BillingReference>';
                $total_pay_tag = 'RequestedMonetaryTotal';

                $line      = 'DebitNoteLine';
                $quantity   = 'DebitedQuantity';
                break;
        }

        $xml =  '<?xml version="1.0" encoding="ISO-8859-1" standalone="no"?>' . $ini_line . '
                <ext:UBLExtensions>
                    <ext:UBLExtension>
                        <ext:ExtensionContent></ext:ExtensionContent>
                    </ext:UBLExtension>
                </ext:UBLExtensions>
                <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
                <cbc:CustomizationID>2.0</cbc:CustomizationID>
                <cbc:ID>' . $sale['serial'] . '-' . $sale['number'] . '</cbc:ID>
                <cbc:IssueDate>' . $sale['issue_date'] . '</cbc:IssueDate>
                <cbc:IssueTime>' . $sale['issue_time'] . '</cbc:IssueTime>';

        if (($sale['due_date'] != null) && (($sale['document_type_code'] == '01') || ($sale['document_type_code'] == '03'))) {

            $xml .= '<cbc:DueDate>' . $sale['due_date'] . '</cbc:DueDate>';
        };

        $xml .= $invoice_type_code . '
        <cbc:Note languageLocaleID="1000">' . $sale['total_letters'] . '</cbc:Note>
        <cbc:DocumentCurrencyCode listID="ISO 4217 Alpha" listName="Currency" listAgencyName="United Nations Economic Commission for Europe">' . $currency_code . '</cbc:DocumentCurrencyCode>' . $credit_note_data;

        foreach ($attached_guides as $guide) {
            $xml .= '
            <cac:DespatchDocumentReference>
                <cbc:ID>' . $guide['guide_serial'] . '-' . $guide['guide_number'] . '</cbc:ID>
                <cbc:DocumentTypeCode listAgencyName="PE:SUNAT" listName="Tipo de Documento" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01">' . $guide['guide_document_code'] . '</cbc:DocumentTypeCode>
            </cac:DespatchDocumentReference>';
        }

        $xml .= '
        <cac:Signature>
            <cbc:ID>' . $company['ruc'] . '</cbc:ID>
            <cac:SignatoryParty>
                <cac:PartyIdentification>
                    <cbc:ID>' . $company['ruc'] . '</cbc:ID>
                </cac:PartyIdentification>
                <cac:PartyName>
                    <cbc:Name><![CDATA[' . $company['business_name'] . ']]></cbc:Name>
                </cac:PartyName>
            </cac:SignatoryParty>
            <cac:DigitalSignatureAttachment>
                <cac:ExternalReference>
                    <cbc:URI>' . $company['ruc'] . '</cbc:URI>
                </cac:ExternalReference>
            </cac:DigitalSignatureAttachment>
        </cac:Signature>

        <cac:AccountingSupplierParty>
            <cac:Party>
                <cac:PartyIdentification>
                    <cbc:ID schemeID="6">' . $company['ruc'] . '</cbc:ID>
                </cac:PartyIdentification>
                <cac:PartyName>
                    <cbc:Name><![CDATA[' . $company['trade_name'] . ']]></cbc:Name>
                </cac:PartyName>
                <cac:PartyLegalEntity>
                    <cbc:RegistrationName><![CDATA[' . $company['business_name'] . ']]></cbc:RegistrationName>
                    <cac:RegistrationAddress>
                        <cbc:ID schemeName="Ubigeos" schemeAgencyName="PE:INEI">' . $company['ubigeo'] . '</cbc:ID>
                        <cbc:AddressTypeCode listAgencyName="PE:SUNAT" listName="Establecimientos anexos">0000</cbc:AddressTypeCode>
                        <cbc:CityName>' . $company['province'] . '</cbc:CityName>
                        <cbc:CountrySubentity>' . $company['department'] . '</cbc:CountrySubentity>
                        <cbc:District>' . $company['district'] . '</cbc:District>
                        <cac:AddressLine>
                            <cbc:Line>' . $company['fiscal_address'] . '</cbc:Line>
                        </cac:AddressLine>
                        <cac:Country>
                            <cbc:IdentificationCode listID="ISO 3166-1" listAgencyName="United Nations Economic Commission for Europe" listName="Country">PE</cbc:IdentificationCode>
                        </cac:Country>
                    </cac:RegistrationAddress>
                </cac:PartyLegalEntity>
            </cac:Party>
        </cac:AccountingSupplierParty>

        <cac:AccountingCustomerParty>
            <cac:Party>
                <cac:PartyIdentification>
                    <cbc:ID schemeID="' . $client['entity_type_code'] . '" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">' . $client['document_number'] . '</cbc:ID>
                </cac:PartyIdentification>
                <cac:PartyLegalEntity>
                    <cbc:RegistrationName><![CDATA[' . $client['legal_name'] . ']]></cbc:RegistrationName>
                </cac:PartyLegalEntity>
            </cac:Party>
        </cac:AccountingCustomerParty>
        ';

        //DETRACCION --- INICO
        if (isset($sale['detraccion_percentage']) && ($sale['detraccion_percentage'] != '') && ($sale['detraccion_percentage'] != null) && ($sale['detraccion_percentage'] > 0)) {
            $xml .= '
            <cac:PaymentTerms>
                <cbc:ID schemeName="SUNAT:Codigo de detraccion" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo54">' . $sale['detraccion_codigo'] . '</cbc:ID>
                <cbc:PaymentPercent>' . $sale['detraccion_percentage'] . '</cbc:PaymentPercent>
                <cbc:Amount currencyID="PEN">' . number_format($total_pay * $sale['detraccion_percentage'] * (0.01)) . '</cbc:Amount>
            </cac:PaymentTerms>';
        }
        //DETRACCION --- FIN

        $global_discount_percentage = isset($sale['global_discount_percentage']) ? $sale['global_discount_percentage'] : 0;

        //Forma de pago  --  INICIO   - solo para facturas y boletas. facturas 01, boletas 03
        if (($sale['document_type_code'] == '01') || ($sale['document_type_code'] == '03') || (($sale['document_type_code'] == '07') && $sale['document_type_code'] == '13')) {
            if ($sale['payment_method_id'] == 1) { //CONTADO
                $xml .= '
                <cac:PaymentTerms>
                    <cbc:ID>FormaPago</cbc:ID>
                    <cbc:PaymentMeansID>Contado</cbc:PaymentMeansID>
                </cac:PaymentTerms>';
            }
            if ($sale['payment_method_id'] == 2) { //CREDITO

                $detraccion = 0;
                if (($sale['detraccion_percentage'] != '') && ($sale['detraccion_percentage'] != null) && ($sale['detraccion_percentage'] > 0)) {
                    $detraccion = $total_pay * $sale['detraccion_percentage'] * (0.01);
                }

                //TODO: quitar
                $total_amount_payments = 0;
                foreach ($payments as $payment) {
                    $total_amount_payments += $payment['payment_amount'];
                }

                $xml .= '
                <cac:PaymentTerms>
                    <cbc:ID>FormaPago</cbc:ID>
                    <cbc:PaymentMeansID>Credito</cbc:PaymentMeansID>
                    <cbc:Amount currencyID="' . $currency_code . '">' . number_format($total_amount_payments, 2, '.', '') . '</cbc:Amount>
                </cac:PaymentTerms>';

                $count = 1;
                foreach ($payments as $payment) {
                    $xml .= '
                    <cac:PaymentTerms>
                        <cbc:ID>FormaPago</cbc:ID>
                        <cbc:PaymentMeansID>Cuota00' . $count . '</cbc:PaymentMeansID>
                        <cbc:Amount currencyID="' . $currency_code . '">' . number_format($payment['payment_amount'], 2, '.', '') . '</cbc:Amount>
                        <cbc:PaymentDueDate>' . $payment['payment_due_date'] . '</cbc:PaymentDueDate>
                    </cac:PaymentTerms>';
                    $count++;
                }
            }
        }
        ///Forma de pago  --  FIN

        // DEASCUENTO GLOBAL
        if ($global_discount_percentage > 0) {
            $xml .= '
            <cac:AllowanceCharge>
                <cbc:ChargeIndicator>false</cbc:ChargeIndicator>
                <cbc:AllowanceChargeReasonCode>02</cbc:AllowanceChargeReasonCode>
                <cbc:MultiplierFactorNumeric>' . $sale['global_discount_percentage'] . '</cbc:MultiplierFactorNumeric>
                <cbc:Amount currencyID="PEN">' . number_format($sale['global_discount_percentage'] * $sale['total_taxable']) . '</cbc:Amount>
                <cbc:BaseAmount currencyID="PEN">' . $sale['total_taxable'] . '</cbc:BaseAmount>
            </cac:AllowanceCharge>
        ';
        }

        $xml .=  '<cac:TaxTotal>
                <cbc:TaxAmount currencyID="' . $currency_code . '">' . number_format($total_igv * (1 - $global_discount_percentage), 2, '.', '') . '</cbc:TaxAmount>';

        if ($sale['total_taxable'] != null) {

            $xml .=  '
            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="' . $currency_code . '">' . number_format($sale['total_taxable'] * (1 - $global_discount_percentage), 2, '.', '') . '</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="' . $currency_code . '">' . number_format($total_igv * (1 - $global_discount_percentage), 2, '.', '') . '</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cac:TaxScheme>
                        <cbc:ID schemeName="Codigo de tributos" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05">1000</cbc:ID>
                        <cbc:Name>IGV</cbc:Name>
                        <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>';
        };

        if ($sale['total_exonerated'] != null) {
            $xml .=  '
            <cac:TaxSubtotal>
                <cbc:TaxableAmount currencyID="' . $currency_code . '">' . $sale['total_exonerated'] . '</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="' . $currency_code . '">0.00</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cac:TaxScheme>
                        <cbc:ID schemeName="Codigo de tributos" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05">9997</cbc:ID>
                        <cbc:Name>EXO</cbc:Name>
                        <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>';
        };

        if ($sale['total_unaffected'] != null) {
            $xml .=  '<cac:TaxSubtotal>
                    <cbc:TaxableAmount currencyID="' . $currency_code . '">' . $sale['total_unaffected'] . '</cbc:TaxableAmount>
                    <cbc:TaxAmount currencyID="' . $currency_code . '">0.00</cbc:TaxAmount>
                    <cac:TaxCategory>
                        <cac:TaxScheme>
                            <cbc:ID schemeName="Codigo de tributos" schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05">9998</cbc:ID>
                            <cbc:Name>INA</cbc:Name>
                            <cbc:TaxTypeCode>FRE</cbc:TaxTypeCode>
                        </cac:TaxScheme>
                    </cac:TaxCategory>
                </cac:TaxSubtotal>';
        };

        $xml .=  '</cac:TaxTotal>';

        // MONTOS TOTALES
        $xml .=  '
        <cac:' . $total_pay_tag . '>
            <cbc:LineExtensionAmount currencyID="' . $currency_code . '">' . number_format(($total_taxable + $total_exonerated + $total_unaffected) * (1 - $global_discount_percentage), 2, '.', '') . '</cbc:LineExtensionAmount>
            <cbc:TaxInclusiveAmount currencyID="' . $currency_code . '">' . number_format(($total_taxable + $total_exonerated + $total_unaffected + $total_igv) * (1 - $global_discount_percentage), 2, '.', '') . '</cbc:TaxInclusiveAmount>
            <cbc:AllowanceTotalAmount currencyID="' . $currency_code . '">0.00</cbc:AllowanceTotalAmount>
            <cbc:ChargeTotalAmount currencyID="' . $currency_code . '">0.00</cbc:ChargeTotalAmount>
            <cbc:PrepaidAmount currencyID="' . $currency_code . '">0.00</cbc:PrepaidAmount>
            <cbc:PayableAmount currencyID="' . $currency_code . '">' . number_format(($total_taxable + $total_exonerated + $total_unaffected + $total_igv) * (1 - $global_discount_percentage), 2, '.', '') . '</cbc:PayableAmount>
        </cac:' . $total_pay_tag . '>';
        $i = 1;

        // $percent = $variables->porcentaje_valor_igv;

        foreach ($items as $item) {
            $icbper             = 00.00;
            $tax_data           = $variables->tax_data($item['igv_type_code']);
            $discount           = 0;
            $percent            = $tax_data['percentage'];

            $priceAmount        = $variables->priceAmount($item['base_price'], $tax_data['tribute_code'], $percent, $icbper, $discount);
            $PriceTypeCode      = ($tax_data['tribute_code'] == 9996) ? '02' : '01';
            $taxAmount          = $variables->taxAmount($item['quantity'], $item['base_price'], $tax_data['tribute_code'], $percent, $discount);
            $price_priceAmount  = $variables->price_priceAmount($item['base_price'], $tax_data['tribute_code'], $discount);

            //sale del catalgo16
            //PriceAmount precio unitario (precio base x (1 + IGV)) + impuesto por 1 bolsa. (en caso no se pague IGV sera 1 + 0).

            $xml .= '
            <cac:' . $line . '>
                <cbc:ID>' . $i . '</cbc:ID>
                <cbc:' . $quantity . ' unitCode="NIU">' . number_format($item['quantity'], 2, '.', '') . '</cbc:' . $quantity . '>
                <cbc:LineExtensionAmount currencyID="' . $currency_code . '">' . number_format($item['quantity'] * ($item['base_price']), 2, '.', '') . '</cbc:LineExtensionAmount>
                <cac:PricingReference>
                    <cac:AlternativeConditionPrice>
                        <cbc:PriceAmount currencyID="' . $currency_code . '">' . abs(number_format($priceAmount, 6, '.', '')) . '</cbc:PriceAmount>
                        <cbc:PriceTypeCode listName="Tipo de Precio" listAgencyName="PE:SUNAT" listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16">' . $PriceTypeCode . '</cbc:PriceTypeCode>
                    </cac:AlternativeConditionPrice>
                </cac:PricingReference>';

            $xml .= '
            <cac:TaxTotal>
                <cbc:TaxAmount currencyID="' . $currency_code . '">' . number_format(($taxAmount + $icbper * $item['quantity']), 2, '.', '') . '</cbc:TaxAmount>
                <cac:TaxSubtotal>
                    <cbc:TaxableAmount currencyID="' . $currency_code . '">' . number_format(($item['base_price']) * $item['quantity'], 2, '.', '') . '</cbc:TaxableAmount>
                    <cbc:TaxAmount currencyID="' . $currency_code . '">' . number_format($taxAmount, 2, '.', '') . '</cbc:TaxAmount>
                    <cac:TaxCategory>
                        <cbc:Percent>' . $percent * 100 . '</cbc:Percent>
                        <cbc:TaxExemptionReasonCode>' . $item['igv_type_code'] . '</cbc:TaxExemptionReasonCode>
                        <cac:TaxScheme>
                            <cbc:ID>' . $tax_data['tribute_code'] . '</cbc:ID>
                            <cbc:Name>' . $tax_data['name'] . '</cbc:Name>
                            <cbc:TaxTypeCode>' . $tax_data['international_code'] . '</cbc:TaxTypeCode>
                        </cac:TaxScheme>
                    </cac:TaxCategory>
                </cac:TaxSubtotal>
            </cac:TaxTotal>';

            $xml .= '
                <cac:Item>
                    <cbc:Description><![CDATA[' . $item['product'] . ']]></cbc:Description>
                    <cac:SellersItemIdentification>
                        <cbc:ID>' . $item['product_code'] . '</cbc:ID>
                    </cac:SellersItemIdentification>
                    <cac:CommodityClassification>
                        <cbc:ItemClassificationCode>' . $item['sunat_code'] . '</cbc:ItemClassificationCode>
                    </cac:CommodityClassification>
                </cac:Item>
                <cac:Price>
                    <cbc:PriceAmount currencyID="' . $currency_code . '">' . abs($price_priceAmount) . '</cbc:PriceAmount>
                </cac:Price>
            </cac:' . $line . '>
            ';

            $i++;
        }

        $xml .=  '</' . $fin_line . '>';

        return $xml;
    }

    function moneda($currency_id)
    {
        $currency_code = 'PEN';
        $description = 'soles';
        switch ($currency_id) {
            case 1:
                $currency_code = 'PEN';
                $description = 'soles';
                break;
            case 2:
                $currency_code = 'USD';
                $description = 'dolares';
                break;
            case 3:
                $currency_code = 'EUR';
                $description = 'euros';
                break;
        }
        return array($currency_code, $description);
    }
}
