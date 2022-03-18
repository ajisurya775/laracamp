<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Checkout;
use App\Http\Requests\User\Checkout\Store;
use Illuminate\Http\Request;
use App\Models\Camp;
use Auth;
use Mail;
use App\Mail\Checkout\AfterCheckout;
use Str;
use Midtrans;

class CheckoutController extends Controller
{
    public function __construct()
    {
        Midtrans\Config::$serverKey = env('MIDTRANS_SERVERKEY');
        Midtrans\Config::$isProduct = env('MIDTRANS_IS_PRODUCTION');
        Midtrans\Config::$isSanitized = env('MIDTRANS_IS_SANITIZED');
        Midtrans\Config::$is3ds = env('MIDTRANS_IS_3DS');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(camp $camp, Request $request)
    {
        if ($camp->isRegisterd) {
            $request->session()->flash('error',"You already registerd on {$camp->title} camp.");
            return redirect(route('user.dashboard'));
        }

        return view('checkout.create',[
            'camp'=>$camp,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Store $request, Camp $camp)
    {
        //mapping user data
        $data = $request->all();
        $data['user_id'] = Auth::id();
        $data['camp_id'] = $camp->id;

        //update data user
        $user = Auth::user();
        $user->email = $data['email'];
        $user->name = $data['name'];
        $user->occopation = $data['occopation'];
        $user->save();

        $checkout = checkout::create($data);
        $this->getSnapRedirect($checkout);
        //sending email
        Mail::to(Auth::user()->email)->send(new AfterCheckout($checkout));

        return redirect(route('checkout.success'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Checkout  $checkout
     * @return \Illuminate\Http\Response
     */
    public function show(Checkout $checkout)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Checkout  $checkout
     * @return \Illuminate\Http\Response
     */
    public function edit(Checkout $checkout)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Checkout  $checkout
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Checkout $checkout)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Checkout  $checkout
     * @return \Illuminate\Http\Response
     */
    public function destroy(Checkout $checkout)
    {
        //
    }
    public function success()
    {
        return view('checkout.success');
    }
    /**
     * Midtrans Handler
     */
    public function getSnapRedirect(Checkout $checkout)
    {
        $orderId = $checkout->id.'_'.Str::random(5);
        $price = $checkout->Camp->price * 1000;
        $checkout->midtrans_booking_code = $orderId;

        $transaction_details = [
            'order_id' => $orderId,
            'gross_amount' => $price
        ];

        $item_details = [
            'id' => $orderId,
            'price' => $price,
            'quantity' =>1,
            'name' => "Payment for {$checkout->Camp->title} camp"
        ];

        $userData = [
            "first_name" => $checkout->User->name,
            "last_name" =>"",
            "address" => $checkout->User->address,
            "city" =>"",
            "postal_code" => "",
            "phone" => $checkout->User->phone,
            "country_code" => "IDN",
        ];

        $customer_details = [
            "first_name" => $checkout->User->name,
            "last_name" =>"",
            "email" => $checkout->User->email,
            "phone" => $checkout->User->phone,
            "billing_address" => $userData,
            "shipping_address" => $userData,
        ];

        $midtrans_params = [
            'transaction_details' => $transaction_details,
            'customer_details' => $customer_details,
            'item_details' => $item_details,
        ];

        try {
            //Get Snap Payment Page Url
            $payment_url = \Midtrans\Snap::createTransaction($params)->redirect_url;
            $checkout->midtrans_url = $paymentUrl;
            $checkout->save;

            return $payment_url;
        } catch (Exception $e) {
            return false;
        }
    }
}
