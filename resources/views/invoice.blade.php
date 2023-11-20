<?xml version="1.0" encoding="ISO-8859-1" standalone="no"?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
    xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
    xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
    xmlns:ccts="urn:un:unece:uncefact:documentation:2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
    xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2"
    xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2"
    xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <ext:UBLExtensions>
        <ext:UBLExtension>
            <ext:ExtensionContent></ext:ExtensionContent>
        </ext:UBLExtension>
    </ext:UBLExtensions>
    <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
    <cbc:CustomizationID>2.0</cbc:CustomizationID>
    <cbc:ID>{{ $invoice->serial }}</cbc:ID>
    <cbc:IssueDate>{{ $invoice->issu_date }}</cbc:IssueDate>
    <cbc:IssueTime>{{ $invoice->issu_time }}</cbc:IssueTime>
    <cbc:InvoiceTypeCode listID="0101" listAgencyName="PE:SUNAT" listName="Tipo de Documento"
        listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01" name="Tipo de Operacion"
        listSchemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo51">{{ $invoice->document_type_code }}
    </cbc:InvoiceTypeCode>
    <cbc:Note languageLocaleID="1000">{{ $invoice->amount_letter }}</cbc:Note>
    <cbc:DocumentCurrencyCode listID="ISO 4217 Alpha" listName="Currency"
        listAgencyName="United Nations Economic Commission for Europe">{{ $invoice->currency_code }}
    </cbc:DocumentCurrencyCode>
    <cac:Signature>
        <cbc:ID>{{ $company->ruc }}</cbc:ID>
        <cac:SignatoryParty>
            <cac:PartyIdentification>
                <cbc:ID>{{ $company->ruc }}</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name>
                    <![CDATA[{{ $company->business_name }}]]>
                </cbc:Name>
            </cac:PartyName>
        </cac:SignatoryParty>
        <cac:DigitalSignatureAttachment>
            <cac:ExternalReference>
                <cbc:URI>{{ $company->ruc }}</cbc:URI>
            </cac:ExternalReference>
        </cac:DigitalSignatureAttachment>
    </cac:Signature>

    <cac:AccountingSupplierParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID="6">{{ $company->ruc }}</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name>
                    <![CDATA[{{ $company->trade_name }}]]>
                </cbc:Name>
            </cac:PartyName>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName>
                    <![CDATA[{{ $company->business_name }}]]>
                </cbc:RegistrationName>
                <cac:RegistrationAddress>
                    <cbc:ID schemeName="Ubigeos" schemeAgencyName="PE:INEI">{{ $company->ubigeo }}</cbc:ID>
                    <cbc:AddressTypeCode listAgencyName="PE:SUNAT" listName="Establecimientos anexos">0000
                    </cbc:AddressTypeCode>
                    <cbc:CityName>{{ $company->province }}</cbc:CityName>
                    <cbc:CountrySubentity>{{ $company->department }}</cbc:CountrySubentity>
                    <cbc:District>{{ $company->district }}</cbc:District>
                    <cac:AddressLine>
                        <cbc:Line>{{ $company->fiscal_address }}</cbc:Line>
                    </cac:AddressLine>
                    <cac:Country>
                        <cbc:IdentificationCode listID="ISO 3166-1"
                            listAgencyName="United Nations Economic Commission for Europe" listName="Country">PE
                        </cbc:IdentificationCode>
                    </cac:Country>
                </cac:RegistrationAddress>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingSupplierParty>

    <cac:AccountingCustomerParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID="{{ $client->entity_type_code }}" schemeName="Documento de Identidad"
                    schemeAgencyName="PE:SUNAT" schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">
                    {{ $client->document_number }}
                </cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName>
                    <![CDATA[{{ $client->legal_name }}]]>
                </cbc:RegistrationName>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingCustomerParty>


    @if ($invoice->payment_method_id == 1)
        <cac:PaymentTerms>
            <cbc:ID>FormaPago</cbc:ID>
            <cbc:PaymentMeansID>Contado</cbc:PaymentMeansID>
        </cac:PaymentTerms>
    @elseif ($invoice->payment_method_id == 2)
        @php
            $total_amount_payments = 0;
            foreach ($payments as $payment) {
                $total_amount_payments += $payment->amount;
            }
        @endphp
        <cac:PaymentTerms>
            <cbc:ID>FormaPago</cbc:ID>
            <cbc:PaymentMeansID>Credito</cbc:PaymentMeansID>
            <cbc:Amount currencyID="' . $currency_code . '">
                {{ number_format($total_amount_payments, 2, '.', '') }}
            </cbc:Amount>
        </cac:PaymentTerms>

        @foreach ($payments as $index => $payment)
            <cac:PaymentTerms>
                <cbc:ID>FormaPago</cbc:ID>
                <cbc:PaymentMeansID>Cuota00{{ $index }}</cbc:PaymentMeansID>
                <cbc:Amount currencyID="' . $currency_code . '">
                    {{ number_format($payment->amount, 2, '.', '') }}
                </cbc:Amount>
                <cbc:PaymentDueDate>' {{ $payment->due_date }} '</cbc:PaymentDueDate>
            </cac:PaymentTerms>
        @endforeach
    @endif

    @if ($invoice->global_discount_percentage > 0)
        <cac:AllowanceCharge>
            <cbc:ChargeIndicator>false</cbc:ChargeIndicator>
            <cbc:AllowanceChargeReasonCode>02</cbc:AllowanceChargeReasonCode>
            <cbc:MultiplierFactorNumeric>{{ $invoice->global_discount_percentage }}</cbc:MultiplierFactorNumeric>
            <cbc:Amount currencyID="{{ $invoice->currency_code }}">
                {{ number_format($invoice->global_discount_percentage * $invoice->total_taxable) }}
            </cbc:Amount>
            <cbc:BaseAmount currencyID="{{ $invoice->currency_code }}">{{ $invoice->total_taxable }}</cbc:BaseAmount>
        </cac:AllowanceCharge>
    @endif


    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="{{ $invoice->currency_code }}">
            {{ number_format($invoice->total_igv * (1 - $invoice->global_discount_percentage), 2, '.', '') }}
        </cbc:TaxAmount>
        <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="{{ $invoice->currency_code }}">
                {{ number_format($invoice->total_taxable * (1 - $invoice->global_discount_percentage), 2, '.', '') }}
            </cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="{{ $invoice->currency_code }}">
                {{ number_format($invoice->total_igv * (1 - $invoice->global_discount_percentage), 2, '.', '') }}
            </cbc:TaxAmount>
            <cac:TaxCategory>
                <cac:TaxScheme>
                    <cbc:ID schemeName="Codigo de tributos" schemeAgencyName="PE:SUNAT"
                        schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo05">1000</cbc:ID>
                    <cbc:Name>IGV</cbc:Name>
                    <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
    </cac:TaxTotal>
    <cac:LegalMonetaryTotal>
        <cbc:LineExtensionAmount currencyID="{{ $invoice->currency_code }}">
            {{ number_format(($invoice->total_taxable + $invoice->total_exonerated + $invoice->total_unaffected) * (1 - $invoice->global_discount_percentage), 2, '.', '') }}
        </cbc:LineExtensionAmount>
        <cbc:TaxInclusiveAmount currencyID="{{ $invoice->currency_code }}">
            {{ number_format(($invoice->total_taxable + $invoice->total_exonerated + $invoice->total_unaffected + $invoice->total_igv) * (1 - $invoice->global_discount_percentage), 2, '.', '') }}
        </cbc:TaxInclusiveAmount>
        <cbc:AllowanceTotalAmount currencyID="{{ $invoice->currency_code }}">0.00</cbc:AllowanceTotalAmount>
        <cbc:ChargeTotalAmount currencyID="{{ $invoice->currency_code }}">0.00</cbc:ChargeTotalAmount>
        <cbc:PrepaidAmount currencyID="{{ $invoice->currency_code }}">0.00</cbc:PrepaidAmount>
        <cbc:PayableAmount currencyID="{{ $invoice->currency_code }}">
            {{ number_format(($invoice->total_taxable + $invoice->total_exonerated + $invoice->total_unaffected + $invoice->total_igv) * (1 - $invoice->global_discount_percentage), 2, '.', '') }}
        </cbc:PayableAmount>
    </cac:LegalMonetaryTotal>

    @foreach ($items as $index => $item)
        <cac:InvoiceLine>
            <cbc:ID>{{ $index + 1 }}</cbc:ID>
            <cbc:InvoicedQuantity unitCode="NIU">1.00</cbc:InvoicedQuantity>
            <cbc:LineExtensionAmount currencyID="{{ $invoice->currency_code }}">
                {{ number_format($item->quantity * $item->base_price, 2, '.', '') }}
            </cbc:LineExtensionAmount>
            <cac:PricingReference>
                <cac:AlternativeConditionPrice>
                    <cbc:PriceAmount currencyID="{{ $invoice->currency_code }}">
                        {{ number_format($item->price_amount, 6, '.', '') }}
                    </cbc:PriceAmount>
                    <cbc:PriceTypeCode listName="Tipo de Precio" listAgencyName="PE:SUNAT"
                        listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo16">
                        {{ $item->price_type_code }}
                    </cbc:PriceTypeCode>
                </cac:AlternativeConditionPrice>
            </cac:PricingReference>
            <cac:TaxTotal>
                <cbc:TaxAmount currencyID="{{ $invoice->currency_code }}">
                    {{ number_format($item->tax_amount + $item->icbper * $item->quantity, 2, '.', '') }}
                </cbc:TaxAmount>
                <cac:TaxSubtotal>
                    <cbc:TaxableAmount currencyID="{{ $invoice->currency_code }}">
                        {{ number_format($item->base_price * $item->quantity, 2, '.', '') }}
                    </cbc:TaxableAmount>
                    <cbc:TaxAmount currencyID="{{ $invoice->currency_code }}">
                        {{ number_format($item->tax_amount, 2, '.', '') }}
                    </cbc:TaxAmount>
                    <cac:TaxCategory>
                        <cbc:Percent>{{ $item->tax_percent }}</cbc:Percent>
                        <cbc:TaxExemptionReasonCode>{{ $item->igv_type_code }}</cbc:TaxExemptionReasonCode>
                        <cac:TaxScheme>
                            <cbc:ID>{{ $item->tax->code }}</cbc:ID>
                            <cbc:Name>{{ $item->tax->name }}</cbc:Name>
                            <cbc:TaxTypeCode>{{ $item->tax->international_code }}</cbc:TaxTypeCode>
                        </cac:TaxScheme>
                    </cac:TaxCategory>
                </cac:TaxSubtotal>
            </cac:TaxTotal>
            <cac:Item>
                <cbc:Description>
                    <![CDATA[{{ $item->product_name }}]]>
                </cbc:Description>
                <cac:SellersItemIdentification>
                    <cbc:ID>{{ $item->product_code }}</cbc:ID>
                </cac:SellersItemIdentification>
                <cac:CommodityClassification>
                    <cbc:ItemClassificationCode>{{ $item->sunat_code }}</cbc:ItemClassificationCode>
                </cac:CommodityClassification>
            </cac:Item>
            <cac:Price>
                <cbc:PriceAmount currencyID="{{ $invoice->currency_code }}">
                    {{ $item->price_amount }}
                </cbc:PriceAmount>
            </cac:Price>
        </cac:InvoiceLine>
    @endforeach
</Invoice>
