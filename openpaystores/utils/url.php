<?php

class Url
{
    const COUNTRY_API_URLS = [
        'MX'=>[
            'sandbox' => 'https://sandbox-api.openpay.mx/v1',
            'production' => 'https://api.openpay.mx/v1'
        ],
        'CO'=>[
            'sandbox'=> 'https://sandbox-api.openpay.co/v1',
            'production' => 'https://api.openpay.co/v1'
        ],
        'PE'=>[
            'sandbox'=>'https://sandbox-api.openpay.pe/v1',
            'production' => 'https://api.openpay.pe/v1'
        ] 
    ];

    const COUNTRY_DASHBOARD_URLS = [
        'MX'=>[
            'sandbox' => 'https://sandbox-dashboard.openpay.mx',
            'production' => 'https://dashboard.openpay.mx'
        ],
        'CO'=>[
            'sandbox'=> 'https://sandbox-api.openpay.co/v1',
            'production' => 'https://sandbox-dashboard.openpay.co'
        ],
        'PE'=>[
            'sandbox'=>'https://sandbox-dashboard.openpay.pe',
            'production' => 'https://dashboard.openpay.pe'
        ] 
    ];

    public static function getApiUrlByCountryCode($countryCode) {
        return self::COUNTRY_API_URLS[$countryCode];
    }

    public static function getDashboardUrlByCountryCode($countryCode) {
        return self::COUNTRY_DASHBOARD_URLS[$countryCode];
    }

    public static function getUrlPdfBase($isProduction, $countryCode){
        $countryCode = strtolower($countryCode);
        $sandbox = 'https://sandbox-dashboard.openpay.'.$countryCode.'/paynet-pdf';
        $production = 'https://dashboard.openpay.'.$countryCode.'/paynet-pdf';
        $pdfBase = ($isProduction) ?  $production : $sandbox; 
        return $pdfBase;   
    }
}