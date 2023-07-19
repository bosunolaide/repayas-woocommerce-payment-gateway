<?php

$gateway_status = $_POST['status'];
$redirect_url = $_POST['redirect_url'];

if ($gateway_status === "success") {
	wp_redirect($redirect_url);
	exit();
}