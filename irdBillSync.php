<?php
include 'NepaliCalender.php';

$seller_pan = $_POST['seller_pan'];
$buyer_pan = $_POST['buyer_pan'];
$buyer_name = $_POST['buyer_name'];
$invoice_number = $_POST['invoice_number'];
$total_sales = $_POST['total_sales'];

//calculate vat and taxable amount
$vat = $total_sales * 0.13;
$taxableAmt = $total_sales - $vat;

//converting english date to nepali date
$customer_invoice_date = date('c');
$nep_invoice_date = NepaliCalender::getInstance()->eng_to_nep($customer_invoice_date);

//adding zero in front of month and date if it is single digit
if (strlen($nep_invoice_date['month']) == 1) {
    $nep_invoice_date['month'] = '0' . $nep_invoice_date['month'];
}
//adding zero in front of date if it is single digit
if (strlen($nep_invoice_date['date']) == 1) {
    $nep_invoice_date['date'] = '0' . $nep_invoice_date['date'];
}
$nep_invoice_date = $nep_invoice_date['year'] . '.' . $nep_invoice_date['month'] . '.' . $nep_invoice_date['date'];

//getting fiscal year
$fas_year = NepaliCalender::getInstance()->eng_to_nep($customer_invoice_date);
$fiscal_year_date = $fas_year['year'] . '.0' . substr($fas_year['year'], 1) + 1;


$curl = curl_init();

$data = array(
    'username' => 'Test_CBMS', //Test Server UserName
    'password' => 'test@321',  //Test Server Password

    'seller_pan' => $seller_pan,
    'buyer_pan' => $buyer_pan,
    'buyer_name' => $buyer_name,
    'fiscal_year' => $fiscal_year_date,
    'invoice_number' => $invoice_number,
    'invoice_date' => $nep_invoice_date,
    'total_sales' => $total_sales,
    'taxable_sales_vat' => $taxableAmt,
    'vat' => $vat,
    'excisable_amount' => 0,
    'excise' => 0,
    'taxable_sales_hst' => 0,
    'hst' => 0,
    'amount_for_esf' => 0,
    'esf' => 0,
    'export_sales' => 0,
    'tax_exempted_sales' => 0,
    'isrealtime' => false,
    'datetimeclient' => date('c')
);

$data_json = json_encode($data);

$headers = array(
    'Accept: application/json',
    'Content-Type: application/json'
);

curl_setopt_array($curl, array(
    CURLOPT_URL => 'https://cbapi.ird.gov.np/api/bill', //Test Server API URL	
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $data_json,
    CURLOPT_HTTPHEADER => $headers
));

$response = curl_exec($curl);

if ($response === false) {
    echo 'Error: ' . curl_error($curl);
} else {
    $result = json_decode($response);
    if ($result != null) {
        if ($result == 200) {
            echo "<script>alert('Bill Synced');</script>";
            echo "<script>window.location='index.html';</script>";
        } elseif ($result == 100) {
            echo "<script>alert('API Credentials Do not Match');</script>";
            echo "<script>window.location='index.html';</script>";
        } elseif ($result == 101) {
            echo "<script>alert('Bill Already Exists');</script>";
            echo "<script>window.location='index.html';</script>";
        } elseif ($result == 102) {
            echo "<script>alert('Exception While Saving Bill Details, Please check model fields and values');</script>";
            echo "<script>window.location='index.html';</script>";
        } elseif ($result == 103) {
            echo "<script>alert('Unknown Exceptions, Please check API URL and model field and values ');</script>";
            echo "<script>window.location='index.html';</script>";
        } elseif ($result == 104) {
            echo "<script>alert('Model Invalid');</script>";
            echo "<script>window.location='index.html';</script>";
        }
    } else {
        echo "<script>alert('API Response Invalid');</script>";
        echo "<script>window.location='index.html';</script>";
    }
}
curl_close($curl);
