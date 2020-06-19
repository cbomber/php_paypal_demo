<?php
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Details;
use PayPal\Api\InputFields;
use PayPal\Api\WebProfile;

require 'bootstrap.php';

$activator = $_GET["activator"];
$invoiceNumber = uniqid();

$price = 2;
$description = "Product Description";
$item = 'Item';
$quantity = 1;
$sku = "item_sku_1234";

$shipping = 0;
$tax = 0;
$subTotal = $price;

$total = $shipping + $tax + $subTotal;

$payer = new Payer();
$payer->setPaymentMethod('paypal');

$item1 = new Item();
$item1->setName($item)
    ->setCurrency('USD')
    ->setQuantity($quantity)
    ->setSku($sku) 
    ->setPrice($price);


$itemList = new ItemList();
$itemList->setItems(array($item1));

$details = new Details();
$details->setTax($tax)
    ->setSubtotal($subTotal);

$amount = new Amount();
$amount->setCurrency("USD")
    ->setTotal($total)
    ->setDetails($details);

$transaction = new Transaction();
$transaction->setAmount($amount)
    ->setItemList($itemList)
    ->setDescription($description)
    ->setInvoiceNumber($invoiceNumber);


$redirectUrls = new RedirectUrls();
$redirectUrls->setReturnUrl($paypalConfig['return_url']."&activator=$activator")
->setCancelUrl($paypalConfig['cancel_url']);


$inputFields = new InputFields();

$inputFields->setAllowNote(true)
    ->setNoShipping(1) // Important step
    ->setAddressOverride(0);

$webProfile = new WebProfile();
$webProfile->setName(uniqid())
    ->setInputFields($inputFields)
    ->setTemporary(true);

$createProfile = $webProfile->create($apiContext);

$payment = new Payment();
$payment->setIntent('sale')
    ->setPayer($payer)
    ->setTransactions([$transaction])
    ->setRedirectUrls($redirectUrls);
$payment->setExperienceProfileId($createProfile->getId()); // Important step.


try {
    $payment->create($apiContext);
} catch (Exception $e) {
    echo "<pre>";
    var_dump($e);
    echo "</pre>";
    throw new Exception('Unable to create link for payment');
}

header('location:' . $payment->getApprovalLink());
exit(1);
  
?>
