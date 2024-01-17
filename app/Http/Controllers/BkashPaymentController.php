<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Karim007\LaravelBkash\Facade\BkashPayment;

class BkashPaymentController extends Controller
{

        private $base_url;
        private $app_key;
        private $app_secret;
        private $username;
        private $password;

        public function __construct()
        {
            // bKash Merchant API Information
            // bkash_username = 'sandboxTokenizedUser02'
            // bkash_password = 'sandboxTokenizedUser02@12345'
            // bkash_api_key = '4f6o0cjiki2rfm34kfdadl1eqq'
            // bkash_secret_key = '2is7hdktrekvrbljjh44ll3d9l1dtjo4pasmjvs5vl5qr3fug4b'

            // You can import it from your Database
            $bkash_app_key = '4f6o0cjiki2rfm34kfdadl1eqq'; // bKash Merchant API APP KEY
            $bkash_app_secret = '2is7hdktrekvrbljjh44ll3d9l1dtjo4pasmjvs5vl5qr3fug4b'; // bKash Merchant API APP SECRET
            $bkash_username = 'sandboxTokenizedUser02'; // bKash Merchant API USERNAME
            $bkash_password = 'sandboxTokenizedUser02@12345'; // bKash Merchant API PASSWORD
            $bkash_base_url = 'https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/'; // For Live Production URL: https://checkout.pay.bka.sh/v1.2.0-beta

            $this->app_key = $bkash_app_key;
            $this->app_secret = $bkash_app_secret;
            $this->username = $bkash_username;
            $this->password = $bkash_password;
            $this->base_url = $bkash_base_url;
        }

        public function getToken()
        {
            session()->forget('bkash_token');

            $post_token = array(
                'app_key' => $this->app_key,
                'app_secret' => $this->app_secret
            );

            $url = curl_init('https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout/token/grant');
            $post_token = json_encode($post_token);
            $header = array(
                'Content-Type:application/json',
                "password:$this->password",
                "username:$this->username"
            );

            curl_setopt($url, CURLOPT_HTTPHEADER, $header);
            curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($url, CURLOPT_POSTFIELDS, $post_token);
            curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
            $resultdata = curl_exec($url);
            curl_close($url);

            $response = json_decode($resultdata, true);

            if (array_key_exists('msg', $response)) {
                return $response;
            }
            // return response()->json(['success', true]);
            return  $response['id_token'];
        }

        public function createPayment(Request $request)
        {
            $token = $this->getToken();

            if (!$request->amount) {
                return response()->json([
                    'errorMessage' => 'Amount Mismatch',
                    'errorCode' => 2006
                ],422);
            }

            $callbackURL='http://localhost:5000/api/bkash/payment/callback';

            $requestbody = array(
                'mode' => '0011',
                'amount' => $request->amount,
                'currency' => 'BDT',
                'intent' => 'sale',
                'payerReference' => ' ',
                'merchantInvoiceNumber' => rand(),
                'callbackURL' => $callbackURL
            );
            $url = curl_init('https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout/create');
             $requestbodyJson = json_encode($requestbody);

            $header = array(
            'Content-Type:application/json',
            'Authorization:'. $token,
            'X-APP-Key:'. $this->app_key
                );

            curl_setopt($url, CURLOPT_HTTPHEADER, $header);
            curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($url, CURLOPT_POSTFIELDS, $requestbodyJson);
            curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($url, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            $resultdata = curl_exec($url);
            curl_close($url);
            $obj = json_decode($resultdata);

            return $obj;
        }

        public function executePayment(Request $request)
        {
            $token = session()->get('bkash_token');

            $paymentID = $request->paymentID;
            $url = curl_init("https://tokenized.sandbox.bka.sh/v1.2.0-beta/tokenized/checkout/create". $paymentID);
            $header = array(
                'Content-Type:application/json',
                "authorization:$token",
                "x-app-key:$this->app_key"
            );

            curl_setopt($url, CURLOPT_HTTPHEADER, $header);
            curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
            $resultdata = curl_exec($url);
            curl_close($url);
            return json_decode($resultdata, true);
        }

        public function queryPayment(Request $request)
        {
            $token = session()->get('bkash_token');
            $paymentID = $request->payment_info['payment_id'];

            $url = curl_init("$this->base_url/checkout/payment/query/" . $paymentID);
            $header = array(
                'Content-Type:application/json',
                "authorization:$token",
                "x-app-key:$this->app_key"
            );

            curl_setopt($url, CURLOPT_HTTPHEADER, $header);
            curl_setopt($url, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
            $resultdata = curl_exec($url);
            curl_close($url);
            return json_decode($resultdata, true);
        }

        public function bkashSuccess(Request $request)
        {
            // IF PAYMENT SUCCESS THEN YOU CAN APPLY YOUR CONDITION HERE
            if ('Noman' == 'success') {

                // THEN YOU CAN REDIRECT TO YOUR ROUTE

                Session::flash('successMsg', 'Payment has been Completed Successfully');

                return response()->json(['status' => true]);
            }

            Session::flash('error', 'Noman Error Message');

            return response()->json(['status' => false]);
        }



}
