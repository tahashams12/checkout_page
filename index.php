<?php
require_once 'vendor/stripe/init.php'; 
include('db.php'); 

use Stripe\Charge;
use Stripe\Stripe;

Stripe::setApiKey('sk_test_51PYaxBKWQhATfha0Dl5Xd9dQgDjuY5qEZSavxrJuJpFkaol6dHwvjOuKCiKjICHr759YGqobPC48iUsnPs4xkhCK00SVRGxtbR');

if (isset($_POST['stripeToken'])) {

    $token = $_POST['stripeToken'];
    $amount = $_POST['amount'];

  
    
    try {
        
        $charge = Charge::create([
            'amount' => $amount * 100, 
            'currency' => 'usd',
            'source' => $token,
            'description' => 'Payment description', 
        ]);

       
        if ($charge->status === 'succeeded') {
          
            $amount = mysqli_real_escape_string($conn, $amount);
            $token = mysqli_real_escape_string($conn, $token);

           
            $sql = "INSERT INTO transactions (amount, currency, token, description, status)
                    VALUES ('$amount', 'usd', '$token', 'Payment description', 'succeeded')";

          
            $result = mysqli_query($conn, $sql);

            if ($result) {
                echo "Payment successful!";
            } else {
                echo "Payment failed: " ;
            }
        }
        
        else {
            echo "Payment failed: An unknown error occurred.";
        }
        $paymentSuccess = true;
    } catch (Exception $e) {
        echo "Payment failed: " . $e->getMessage();
        $paymentSuccess = false;
    }

   
    mysqli_close($conn);

    
    if ($paymentSuccess) {
        $_POST['stripeToken']=null;
        echo "<script>
                alert('Payment successful!');
                window.location.href = 'index.php';
              </script>";
            exit();

    } else {
        $_POST['stripeToken']=null;
        echo "<script>
        alert('Payment failed!');
        window.location.href = 'index.php';
      </script>";
      exit();
   
    }

   
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stripe Payment Form with Google Pay</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://js.stripe.com/v3/"></script>
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4 text-center">Stripe Payment Form</h2>
    <form id="payment-form" method="post" action="index.php">
        <div class="form-group">
            <label for="card-element">Credit or Debit Card</label>
            <div class="form-control"></div>
        </div>
        <div class="form-group">
            <label for="amount">Amount (USD)</label>
            <input type="number" class="form-control" id="amount" name="amount" required>
        </div>
        <button class="btn btn-primary btn-block" type="submit">Submit Payment</button>
    </form>
</div>

<script>
    const stripe = Stripe('pk_test_51PYaxBKWQhATfha0ZGw2UcZB6OumXjJRh2MJIlpQbQJfQrDZoNQmnToqXHLjPuQZOHk4LZAt9UZV4Dlxse3GIXfL00akmAHl15');
    const elements = stripe.elements();
    const card = elements.create('card');
    card.mount('.form-control');

    document.getElementById('payment-form').addEventListener('submit', async function(event) {
        event.preventDefault();
        const { token } = await stripe.createToken(card);
        
        const form = document.getElementById('payment-form');
        const hiddenInput = document.createElement('input');
        hiddenInput.setAttribute('type', 'hidden');
        hiddenInput.setAttribute('name', 'stripeToken');
        hiddenInput.setAttribute('value', token.id);
        form.appendChild(hiddenInput);
        form.submit();
    });
</script>

</body>
</html>
