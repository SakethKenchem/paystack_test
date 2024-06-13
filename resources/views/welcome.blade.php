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

    <!-- Refund Form -->
    <h2>Refund</h2>
    <form id="refundForm">
        <div class="form-group">
            <label for="transaction-id">Transaction ID</label>
            <input type="text" id="transaction-id" required />
        </div>
        <div class="form-submit">
            <button type="button" onclick="refundPayment()"> Refund </button>
        </div>
    </form>
      
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <script>
        const paymentForm = document.getElementById('paymentForm');
        paymentForm.addEventListener("submit", payWithPaystack, false);

        function payWithPaystack(e) {
            e.preventDefault();

            let handler = PaystackPop.setup({
                key: '{{ env('PAYSTACK_PUBLIC_KEY') }}', 
                email: document.getElementById("email-address").value,
                amount: 850 * 100 * document.getElementById("quantity").value, 
                currency: 'KES', 
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
                    window.location.href = "{{ route('callback') }}?reference=" + response.reference;
                }
            });

            handler.openIframe();
        }

        function refundPayment() {
            let transactionId = document.getElementById("transaction-id").value.trim();

            if (transactionId === "") {
                alert("Please enter a transaction ID.");
                return;
            }

            // Make an AJAX request to initiate the refund
            fetch(`/refund/${transactionId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ transaction_id: transactionId })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing your request.');
            });
        }
    </script>
</body>
</html>
