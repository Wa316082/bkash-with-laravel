<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Laravel</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
        crossorigin="anonymous"></script>
        <script src="https://scripts.sandbox.bka.sh/versions/1.1.0-beta/checkout/bKash-checkout-sandbox.js"></script>
        <!-- Styles -->
    </head>
    <body class="antialiased " style="min-height: 100vh; min-width: 100vw;">
        <button id="bKash_button" onclick="BkashPayment()">Pay With bKash</button>

        {{-- <a class="btn btn-success" href="{{ url('bkash/create-payment') }}">Payment</a> --}}
    </body>


    <script src="https://code.jquery.com/jquery-3.4.1.min.js"
        integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous">
    </script>

    <script>
        function BkashPayment() {
            $.ajax({
                    url: '{{ route('bkash-create-payment') }}',
                    headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                    data: JSON.stringify({amount:50}),
                    type: 'POST',
                    contentType: 'application/json',
                    success: function (data) {
                        // hideLoading();
                        window.location.href = data.bkashURL

                    },
                    error: function (err) {
                        console.log(err);
                        // hideLoading();

                        // showErrorMessage(err.responseJSON);
                        // bKash.create().onError();
                    }
                });

            }
    </script>

</html>
