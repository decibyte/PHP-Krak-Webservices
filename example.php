<?php

include('krak-webservice.php');

$phone = '80808080';

$kws = new KrakWebservice('username', 'password', 'product_id'); // Replace with your own username, password, product_id.

$data = $kws->get_tele_by_tn($phone);

if ($data)
{
	print $data->CompanyName ? $data->CompanyName : $data->FirstName . ' ' . $data->LastName;
	print ' from ';
	print $data->Address->PostalDistrict;
}
else
{
	print $phone . ' not found';
}

?>
