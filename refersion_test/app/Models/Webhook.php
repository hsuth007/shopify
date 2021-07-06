<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class Webhook extends Model
{
    /** 
    * notify Refersion webhook
    * 
    * @return mixed
    */
	
    public function getProductSku() {
        
        $baseUrl = "/admin/api/2021-04/";
        
        $products = "products.json";
        
        $affiliateId = null;
                
        $url = 'https://' . env('SHOPIFY_KEY') . ':' . env('SHOPIFY_SECRET') . '@' . env('SHOPIFY_DOMAIN') . $baseUrl . $products;
        
        $response = Http::timeout(3)->get($url);
        
        $data = $response->json();
        
        foreach($data['products'] as $product) {
            foreach($product['variants'] as $key => $val) {
                if(str_contains($val['sku'], 'rfsnadid') !== false) {
                   $affiliateId = substr($val['sku'], strpos($val['sku'], ":") + 1);
                }
                else {
                    $affiliateId = "No Refersion identifier detected in sku!";
                }
            }
        }
        return json_encode($affiliateId);
    }
    
    public function calculateHmac($data, $hmac_header)
    {
        $calculated_hmac = base64_encode(hash_hmac('sha256', $data, env('SHOPIFY_WEBHOOK_SECRET'), true));
        return hash_equals($hmac_header, $calculated_hmac);
    }
    /*
     * @$msg bool
     */
    //Success verified
    public function verifiedWebhook()
    {
        $hmac_header = $_SERVER['HTTP_X_SHOPIFY_HMAC_SHA256'];
        $input = file_get_contents('php://input');
        $verified = calculateHmac($input, $hmac_header);
        $log = error_log('Webhook verified: ' . var_export($verified, true));
        
        $msg = $verified === true ?: false;
        
        return $msg;
    }
}
