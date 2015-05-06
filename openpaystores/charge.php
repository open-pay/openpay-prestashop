<?php

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');
require_once(dirname(__FILE__).'/openpaystores.php');

if (!defined('_PS_VERSION_'))
	exit;

/* Todo: extend for multiple cash payment points 7 Eleven, Extra, SPEI, etc */
    
$opnepay = new OpenpayStores();

/* Check that module is active */
if ($opnepay->active)
	$opnepay->processPayment();
else
	Tools::dieOrLog('error unable to process payment.', true);
    
    
