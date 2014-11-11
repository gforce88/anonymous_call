<?php

// # Get Credit Card Sample
// The CreditCard resource allows you to
// retrieve previously saved CreditCards.
// API called: '/v1/vault/credit-card'
// The following code takes you through
// the process of retrieving a saved CreditCard
require __DIR__ . '/../bootstrap.php';
use PayPal\Api\CreditCard;

// The cardId can be obtained from a previous save credit
// card operation. Use $card->getId()
$cardId = "CARD-44D10970C24287906KRCTWNI";

/// ### Retrieve card
// (See bootstrap.php for more on `ApiContext`)
try {
	$card = CreditCard::get($cardId, $apiContext);
} catch (PayPal\Exception\PPConnectionException $ex) {
	echo "Exception: " . $ex->getMessage() . PHP_EOL;
	var_dump($ex->getData());
	exit(1);
}
?>
<html>
<head>
	<title>Lookup a saved credit card</title>
</head>
<body>
	<div>Retrieving saved credit card: <?php echo $cardId;?></div>
	<pre><?php echo $card->toJSON(128);?></pre>
	<a href='../index.html'>Back</a>
</body>
</html>
