<!DOCTYPE html>
<html lang="en">
<head>
  <title>Bootstrap Example</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
	<body>
		<div class="container" style="padding-top: 50px;">
			<div style="text-align: center;">
				<form action='https://www.sandbox.paypal.com/cgi-bin/webscr' method='post' name='frmPayPal1'>
					<input type='hidden' name='business' value='mobileappz007-facilitator@gmail.com'>
					<input type='hidden' name='cmd' value='_xclick'>
					<input type='hidden' name='item_name' value='<?php echo $pkgName; ?>'>
					<input type='hidden' name='item_number' value='<?php echo $pkgId; ?>'>
					<input type='hidden' name='amount' value='<?php echo $amount; ?>'>
					<input type='hidden' name='no_shipping' value='0'>
					<input type='hidden' name='currency_code' value='USD'>
					<input type='hidden' name='handling' value='0'>
					<input type='hidden' name='cancel_return' value="<?php echo base_url('Webservice/payufailure'); ?>">
					<input type='hidden' name='return' value="<?php echo base_url('Webservice/payusuccess'); ?>">
					<!-- <input type="image" src="https://www.sandbox.paypal.com/en_US/i/btn/btn_buynowCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
					<img alt="" border="0" src="https://www.sandbox.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1"> -->
					<input type="submit" class="btn btn-lg btn-primary" name="submit" value="Pay Now">
				</form> 
			</div>
		</div>
	</body>
</html>