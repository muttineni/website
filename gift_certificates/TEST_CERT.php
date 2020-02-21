<?php
echo 'It Works!<br>';
echo '<pre>';
print_r($_SESSION);
echo '</pre>';

$order_details = $_SESSION['form_fields']['order_details_array'];
$discount_price_total = 0;
foreach ($order_details as $key => $value) {
		if ($value['amount_due'] < 5000) {
			$discount_price_total += $value['amount_due'];
			echo $value['amount_due'];
			echo '<br>';
		}
		else{
			$discount_price_total += 5000;
			echo 'High Price!';
			echo '<br>';

		}
	}
echo $discount_price_total;
unset($_SESSION['check_cert_response']);
?>