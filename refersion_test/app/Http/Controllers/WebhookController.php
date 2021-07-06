<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use Illuminate\Support\Facades\Http;

class WebhookController extends Controller
{
    /*
     * process and return sku
     */
    public function handle()
    {
        $webhook = new Webhook();
        $sku = json_decode($webhook->getProductSku());
        $data = [
          "affiliate_code" => $sku,
          "type"           => "SKU",
          "trigger"        => "CODE100"
        ];
        return json_encode($data);
    }
    
    /*
     * Result to be sent to refersion new affiliate trigger endpoint 
     */
    public function newAffiliateTrigger() {
        $response = Http::withHeaders([
          'accept'               => 'application/json',
          'Refersion-Public-Key' => env(REFERSION_PUBLIC_KEY),
          'Refersion-Secret-Key' => env(REFERSION_SECRET_KEY)
        ])->post('https://www.refersion.com/api/new_affiliate_trigger', [
            $this->handle()
        ]);
        
        if ($response->successful()) {
            $data = $response->successful()->json();
        }
        else {
            $data = $response->failed()->json();
        }
        return $data;
    }
}
