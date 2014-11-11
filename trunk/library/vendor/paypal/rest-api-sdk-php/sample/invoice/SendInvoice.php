<?php

// # Create Invoice Sample
// This sample code demonstrate how you can send
// a legitimate invoice to the payer

require __DIR__ . '/../bootstrap.php';

use PayPal\Api\Invoice;

try {
    // ### Retrieve Invoice
    // Retrieve the invoice object by calling the
    // static `get` method
    // on the Invoice class by passing a valid
    // Invoice ID
    // (See bootstrap.php for more on `ApiContext`)
    $invoice = Invoice::get("INV2-W4LC-6QS9-JZ62-VE4P", $apiContext);

    // ### Send Invoice
    // Send a legitimate invoice to the payer
    // with a valid ApiContext (See bootstrap.php for more on `ApiContext`)
    $sendStatus = $invoice->send($apiContext);
} catch (PayPal\Exception\PPConnectionException $ex) {
    echo "Exception: " . $ex->getMessage() . PHP_EOL;
    var_dump($ex->getData());
    exit(1);
}

?>
<html>
<head>
    <title>Send Invoice</title>
</head>
<body>
<div>Send Invoice:</div>
<pre><?php echo $invoice->toJSON(128); ?></pre>
<a href='../index.html'>Back</a>
</body>
</html>
