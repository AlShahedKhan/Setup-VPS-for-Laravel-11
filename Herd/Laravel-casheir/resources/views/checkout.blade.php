<form id="payment-form" method="POST">
    <input id="card-holder-name" type="text" class="form-control" placeholder="Cardholder Name">

    <!-- Stripe Element -->
    <div id="card-element" class="form-control"></div>
    <br>
    <div id="card-errors" role="alert" style="color: red;"></div>

    <div class="text-center">
        <button id="submit" class="btn btn-lg btn-success">Submit Payment</button>
    </div>
</form>
<script src="https://js.stripe.com/v3/"></script>
<script>
    const stripe = Stripe('{{ config('services.stripe.key') }}');
    const elements = stripe.elements();

    const cardElement = elements.create('card');
    cardElement.mount('#card-element');

    const submitButton = document.getElementById('submit');

    submitButton.addEventListener('click', async (event) => {
        event.preventDefault();

        const clientSecret = '{{ $intent->client_secret }}'; // Get the client secret

        // Confirm the PaymentIntent using the client secret
        const {
            paymentIntent,
            error
        } = await stripe.confirmCardPayment(clientSecret, {
            payment_method: {
                card: cardElement,
                billing_details: {
                    name: document.getElementById('card-holder-name').value,
                }
            }
        });

        if (error) {
            // Display error
            document.getElementById('card-errors').textContent = error.message;
        } else if (paymentIntent.status === 'succeeded') {
            // Payment was successful
            alert('Payment successful!');
        } else if (paymentIntent.status === 'requires_action' || paymentIntent.status ===
            'requires_source_action') {
            // Redirect required (handled by Stripe)
            alert('Redirecting for authentication...');
        }
    });
</script>
