<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\Request;

class CryptoConversionController extends Controller
{
    public function cryptoConvert(Request $request)
    {
        // Validate the request
        $request->validate([
            'crypto_type' => 'required|string',
            'crypto_amount' => 'required|numeric|min:0',
            'fiat_currency' => 'required|string'
        ]);

        
        $apiKey = env('COINMARKETCAP_API_KEY'); 
        $client = new Client();
        $cryptoType = strtoupper($request->crypto_type);
        $fiatCurrency = strtoupper($request->fiat_currency);

        try {
            $response = $client->request('GET', 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest', [
                'query' => [
                    'symbol' => $cryptoType, 
                    'convert' => $fiatCurrency 
                ],
                'headers' => [
                    'X-CMC_PRO_API_KEY' => $apiKey, 
                    'Accept' => 'application/json'
                ]
            ]);

            $data = json_decode($response->getBody(), true);


            $priceInFiat = $data['data'][$cryptoType]['quote'][$fiatCurrency]['price'];


            $cryptoAmount = $request->crypto_amount;
            $convertedAmount = $cryptoAmount * $priceInFiat;

            return response()->json([
                'crypto_amount' => $cryptoAmount,
                'crypto_type' => $cryptoType,
                'fiat_currency' => $fiatCurrency,
                'converted_amount' => $convertedAmount
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch data. ' . $e->getMessage()], 500);
        }
    }
}
