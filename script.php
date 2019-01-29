<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
require 'vendor/autoload.php';
include "./class.connection.php";
include "./class.strategies.php";
$climate = new \League\CLImate\CLImate;
$climate->red('PHP AI Trader.');

$exchange = new Exchange;
$strategies = new Strategies;
//print_r($exchange->getBalance("LUN"));
$strategies->exchange = $exchange->api();

$strategies->exchange->chart(["ETHBTC","PPTBTC","LUNBTC"], "1m", function($api, $symbol, $chart) use ($strategies){
	//echo "{$symbol} chart update\n";
	//print_r($chart);

	$strategies->setData($chart, $symbol);
	$strategies->rsi_adx_bb();
});
/*
$api->kline(["BTCUSDT", "EOSBTC"], "1m", function($api, $symbol, $chart) {
  //echo "{$symbol} ({$interval}) candlestick update\n";
	$interval = $chart->i;
	$tick = $chart->t;
	$open = $chart->o;
	$high = $chart->h;
	$low = $chart->l;
	$close = $chart->c;
	$volume = $chart->q; // +trades buyVolume assetVolume makerVolume
	echo "{$symbol} price: {$close}\t volume: {$volume}\n";
});
*/
?>