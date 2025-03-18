<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stripe\Checkout\Session;
use Stripe\Stripe;



Route::get('/', function () {
    return view('welcome');
});

Route::get('login', function () {
    $email = 'test@test.com';
    $password = '12345678';

    // Attempt login
    if (Auth::attempt(['email' => $email, 'password' => $password])) {
        return redirect('/home');
    }

    dd('not logged in');
});

Route::get('subscriptions/resume', function () {
    $user = Auth::user();
    $activeDefultSubscription = $user->subscription('default');

    $activeDefultSubscription->resume();

    dd('Subscription resumed!');
});

// cancel subscription start
/*
Route::get('login', function () {
    $email = 'test@test.com';
    $password = '12345678';

    // Attempt login
    if (Auth::attempt(['email' => $email, 'password' => $password])) {
        return redirect('/home');
    }

    dd('not logged in');
});
*/
Route::get('subscriptions/cancel', function () {
    $user = Auth::user();
    $activeDefultSubscription = $user->subscription('default');

    // $activeDefultSubscription->cancel();//cancel
    $activeDefultSubscription->cancelNow();

    dd('Subscription cancelled!');
});

// cancel subscription end


/*
// Trail subscription start
Route::get('subscriptions/trail', function (Request $request) {


    // Fetch all subscription start
    $user = auth()->user();
    return $user->subscription('trail-subscription');
    // Fetch all subscription end


    auth()->user()->newSubscription(
        'trail-subscription', 'price_1QuSBxDgYV6zJ17v6qKwZQhC'
    )
    ->trialDays(3)
    ->create($request->payment_method);

    dd('You are subscribed for trail subscription!');

})->name('subscriptions.trail');
// Trail subscription end
*/



// custom recurring payment start with 3d auth Start
// Route::get('login', function () {
//     $email = 'test@example.com';
//     $password = 'password';

//     // Attempt login
//     if (Auth::attempt(['email' => $email, 'password' => $password])) {
//         return redirect('/home');
//     }

//     dd('not logged in');
// });
// Route::get('subscriptions', function () {

//     $intent = auth()->user()->createSetupIntent();

//     return view('subscriptions', compact('intent'));
// });
// Route::post('subscriptions/create', function (Request $request) {

//     auth()->user()->newSubscription(
//         'default', 'price_1QuSBxDgYV6zJ17v6qKwZQhC'
//     )->create($request->payment_method);

//     dd('Your successfully subscribed!');

// })->name('subscriptions.create');
// custom recurring payment start with 3d auth End



/*
// Custom payment start
Route::get('login', function () {
    $email = 'test@example.com';
    $password = 'password';

    if (Auth::attempt(['email' => $email, 'password' => $password])) {
        return redirect('/home');
    }

    dd('not login');
});

Route::get('checkout', function () {
    $amount = 35 * 100;
    // dd(Auth::user());
    $intent = Auth::user()->pay(
        $amount
    );
    return view('checkout', compact('intent'));
});
// custom payment end
*/

// Working fine for one time payment start
// Route::get('login', function () {
//     $email = 'test@example.com';
//     $password = 'password';

//     if (Auth::attempt(['email' => $email, 'password' => $password])) {
//         return redirect('/home');
//     }

//     dd('not login');
// });
// Route::get('/checkout', function (Request $request) {

//     $user  = auth()->user();
//     // dd($user);
//     // For lemon max
//     $stripePriceId = 'price_1QuCfwDgYV6zJ17vStqPMoRf';

//     $quantity = 1;

//     return $user->checkout([$stripePriceId => $quantity], [
//         'success_url' => route('checkout-success'),
//         'cancel_url' => route('checkout-cancel'),
//     ]);
// })->name('checkout');


// Route::get('/checkout/success', function(){
//     return 'success page';
// })->name('checkout-success');
// Route::get('/checkout/cancel', function(){
//     return 'cancel page';
// })->name('checkout-cancel');
// Working fine for one time payment end





// Recurring subscription system start
/*
Route::get('login', function () {
    $email = 'test@example.com';
    $password = 'password';

    if (Auth::attempt(['email' => $email, 'password' => $password])) {
        return redirect('/home');
    }

    dd('not login');
});


Route::get('/checkout', function (Request $request) {
    $user = auth()->user();

    // Set your secret Stripe API key
    Stripe::setApiKey(config('cashier.secret')); // This assumes you have your secret key in .env

    // For recurring payment, use the Stripe price ID for a subscription
    $stripePriceId = 'price_1QuD4MDgYV6zJ17vj6BrIupE'; // Replace with your actual recurring price ID
    $quantity = 1;

    // Create a Stripe customer if one does not exist
    if (!$user->hasStripeId()) {
        $user->createAsStripeCustomer();
    }

    // Create a Stripe Checkout session
    $checkoutSession = Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [
            [
                'price' => $stripePriceId,
                'quantity' => $quantity,
            ],
        ],
        'mode' => 'subscription', // Set the mode to subscription for recurring payments
        'customer' => $user->stripe_id, // Attach the Stripe customer ID
        'success_url' => route('checkout-success'),
        'cancel_url' => route('checkout-cancel'),
    ]);

    // Return the redirect URL for the checkout session
    return redirect($checkoutSession->url);
})->name('checkout');

Route::get('/checkout/success', function () {
    // return 'Subscription successful!';
    return redirect('https://www.ksquaredsourcedcity.com/');
})->name('checkout-success');

Route::get('/checkout/cancel', function () {
    return 'Subscription cancelled!';
})->name('checkout-cancel');
*/
// Recurring subscription system End





// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

// require __DIR__.'/auth.php';
