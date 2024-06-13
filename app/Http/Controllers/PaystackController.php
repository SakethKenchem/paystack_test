<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use App\Models\Payment;

use Illuminate\Http\Request;

class PaystackController extends Controller
{
    public function callback(Request $request)
    {

        //dd($request->all());
        $reference = $request->reference;

        $secret_key = env('PAYSTACK_SECRET_KEY');

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/transaction/verify/".$reference,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $secret_key",
                "Cache-Control: no-cache",
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $response = json_decode($response);

        //dd($response->data->metadata->custom_fields);
        $meta_data = $response->data->metadata->custom_fields;
        


        if($response->data->status == 'success') {
            
            $obj = new Payment;

            $obj->payment_id = $reference;
            $obj->product_name = $meta_data[0]->value;
            $obj->quantity = $meta_data[1]->value;
            $obj->amount = $response->data->amount / 100;
            $obj->currency = $response->data->currency;
            $obj->payment_status = $response->data->status;
            $obj->payment_method = "Paystack";
            $obj->save();
            
            return redirect()->route('success');
        }
        else {
            return redirect()->route('cancel');
        }

        
    }

    public function refund($payment_id)
    {
        $payment = Payment::where('payment_id', $payment_id)->first();

        if (!$payment) {
            return "Payment not found";
        }

        $secret_key = env('PAYSTACK_SECRET_KEY');

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.paystack.co/refund",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode(array(
                'transaction' => $payment_id,
                'amount' => $payment->amount * 100, // Amount in kobo (multiply by 100)
            )),
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer $secret_key",
                "Content-Type: application/json",
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $response = json_decode($response);

        if ($response->status) {
            // Update payment status to refunded in your database
            $payment->update(['payment_status' => 'refunded']);

            return "Refund successful";
        } else {
            return "Refund failed: " . $response->message;
        }
    }

    public function success()
    {
        return "Payment was successful";
    }

    public function cancel()
    {
        return "Payment was cancelled";
    }
}
