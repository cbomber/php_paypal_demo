<?php
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;

require 'bootstrap.php';

if (isset($_GET['success']) && $_GET['success'] == 'true') {

	if (empty($_GET['paymentId']) || empty($_GET['PayerID'])) {
		throw new Exception('The response is missing the paymentId and PayerID');
	}

	$paymentId = $_GET['paymentId'];
	$payment = Payment::get($paymentId, $apiContext);

	$execution = new PaymentExecution();
	$execution->setPayerId($_GET['PayerID']);

	try {

		// Take the payment
		$payment->execute($execution, $apiContext);

		try {	
			$payment = Payment::get($paymentId, $apiContext);
	
			$data = [
				'transaction_id' => $payment->getId(),
				'payment_amount' => $payment->transactions[0]->amount->total,
				'payment_status' => $payment->getState(),
				'invoice_id' => $payment->transactions[0]->invoice_number
			];
			if (addThisPayment($data, $_GET["activator"]) !== false && $data['payment_status'] === 'approved') {
				// Payment successfully added, redirect to the payment complete page.
				$msg = "Payment was successful. Licenses Purchased.";
				$alert = "Success";		
			} else {
				$msg = "Payment was not successful. Please contact an administrator.";
				$alert = "Danger";
			}
	
		} catch (Exception $e) {
			// Failed to retrieve payment from PayPal
			echo $e->getCode()."<hr>"; 
			echo $e->getData()."<hr>"; 
			die("<pre>".$e."</pre>");
	
		}
	
	} catch (Exception $e) {
		// Failed to take payment
		echo $e->getCode()."<hr>"; 
		echo $e->getData()."<hr>"; 
		die("<pre>".$e."</pre>");

	}
} else {
	$msg = "Purchase canceled.";
	$alert = "Info";
}

header("Location: http://" . $_SERVER['SERVER_NAME']."/main/dashboard?alert=$alert&msg=".urlencode($msg));

//Written in CTI framework for database communication. Will need to be transcribed to PDO methods.
function addThisPayment($data, $type){

	if(is_array($data)) {

		$qs = new ctSqlInsert();
		$qs->INSERT("user_payments");
		$qs->setClause("UserID", getUserId());
		$qs->setClause("transaction_id", $data['transaction_id']);
		$qs->setClause("payment_amount", $data['payment_amount']);
		$qs->setClause("payment_status", $data['payment_status']);
		$qs->setClause("invoice_id", $data['invoice_id']);
		$qs->setClause("payment_datetime", date('Y-m-d H:i:s'));		

		switch($type){
			case "btnUpgrade":
				if($qs->execute() > 0){
					return upgradeAccount(getUserId(), $data['transaction_id']);
				}
			break;
			default:
			if($qs->execute() > 0){
				return addLicense(getUserId(), "Full", $data['transaction_id']);
			}
			break;
		}

			return false;

	} 

	return false;

}



?>