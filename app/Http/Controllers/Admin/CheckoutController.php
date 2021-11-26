<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Checkout;
use Mail;
use App\Mail\Checkout\paid;

class CheckoutController extends Controller
{
    public function update(Request $request, Checkout $checkout)
    {
        $checkout->is_paid = true;
        $checkout->save();

        // sending to user mail
        Mail::to($checkout->User->email)->send(new Paid($checkout));
        
        $request->session()->flash('success', "Checkout with id {$checkout->id} has been update!");
        return redirect(route('admin.dashboard'));
    }
}
