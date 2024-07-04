<?php
require_once 'vendor/stripe/init.php'; 
include('db.php'); 

use Stripe\Charge;
use Stripe\Stripe;
use Stripe\Coupon;


Stripe::setApiKey('sk_test_51PYaxBKWQhATfha0Dl5Xd9dQgDjuY5qEZSavxrJuJpFkaol6dHwvjOuKCiKjICHr759YGqobPC48iUsnPs4xkhCK00SVRGxtbR');
$amount = 40;
    
if (isset($_POST['stripeToken'])) {

    $token = $_POST['stripeToken'];
    $coupon_code = $_POST['coupon_code']; 
    $valid=false;
 

if(isset($coupon_code) && !empty($coupon_code)){
    $coupon = Coupon::retrieve($coupon_code);
    if($coupon->valid){
    $amount = $amount - ($amount * $coupon->percent_off / 100);
    $_POST['coupon_code']=null;
    $valid=true;
    }

else{
    
    echo "<script>
    alert('Coupon code is invalid');
    window.location.href = 'index.php';
    </script>";
    $_POST['stripeToken']=null;
    $_POST['coupon_code']=null;
    exit();
    
}

}

    
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


        echo"hey";
        $_POST['stripeToken']=null;
        

    if($valid){
        echo"hey2";
        echo "<script>
                            alert('Coupon code is valid. You got " . $coupon->percent_off . "% off! Payment Successful!');
                            window.location.href = 'index.php';
                          </script>";
        $valid=false;
    }

    else{
        echo "<script>
                alert('Payment successful!');
                window.location.href = 'index.php';
              </script>";
    }

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
            <label for="amount">Coupon-Code</label>
            <input type="text" class="form-control" name="coupon_code" >
        </div>

        <button class="btn btn-primary btn-block" type="submit">Pay <?php echo $amount . "$" ?></button>
    
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
