<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Laravel Paystack</title>
</head>
<body>
    <h2>Product: Laptop</h2>
    <p>Specs: 8GB RAM, 1TB Storage, 14inch 1080p Display</p>
    <p>Price: KES 850</p>

    <form id="paymentForm">
        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email-address" required />
        </div>
        <div class="form-group">
            <label for="first-name">First Name</label>
            <input type="text" id="first-name" />
        </div>
        <div class="form-group">
            <label for="last-name">Last Name</label>
            <input type="text" id="last-name" />
        </div>
        <div class="form-group">
            <label for="quantity">Quantity</label>
            <input type="number" id="quantity" value="1" />
        </div>
        <div class="form-submit">
            <button type="submit" onclick="payWithPaystack()"> Pay </button>
        </div>
    </form>
      
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script>
        const paymentForm = document.getElementById('paymentForm');
        paymentForm.addEventListener("submit", payWithPaystack, false);

        function payWithPaystack(e) {
            e.preventDefault();

            let handler = PaystackPop.setup({
                key: '{{ env('PAYSTACK_PUBLIC_KEY') }}', // Replace with your actual public key
                email: document.getElementById("email-address").value,
                amount: 850 * 100 * document.getElementById("quantity").value, // Amount in cents (kobo)
                currency: 'KES', // Specify currency as KES
                metadata: {
                    custom_fields: [
                        {
                            display_name: "Laptop",
                            variable_name: "laptop",
                            value: "Laptop"
                        },
                        {
                            display_name: "First Name",
                            variable_name: "first_name",
                            value: document.getElementById("first-name").value
                        },
                        {
                            display_name: "Last Name",
                            variable_name: "last_name",
                            value: document.getElementById("last-name").value
                        },
                        {
                            display_name: "Quantity",
                            variable_name: "quantity",
                            value: document.getElementById("quantity").value
                        }
                    ]
                },
                onClose: function(){
                    alert('Window closed.');
                },
                callback: function(response){
                    // let message = 'Payment complete! Reference: ' + response.reference;
                    // alert(message);
                    window.location.href = "{{ route('callback') }}?reference=" + response.reference;
                }
            });

            handler.openIframe();
        }
    </script>
</body>
</html>
