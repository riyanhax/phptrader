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
$strategies->exchange = $exchange;
$strategies->loadconfig();
$strategies->exchange->makeExchangeInfo();
$strategies->pairInfo = json_decode(file_get_contents(__DIR__."/infoexchange.json"));
$pair = json_decode(file_get_contents(__DIR__."/symbol.json"));

$pairs = [];
foreach ($pair as $key => $value) {
	$pairs[] = $key;
}
$strategies->setSymbolConfig($pair);
$strategies->getDefaultTrend();
$arvStart = $strategies->exchange->getBalances();
file_put_contents(__DIR__."/balance.json", json_encode($arvStart)) ;

//$strategies->test_buy("LUNBTC",5);exit();
$strategies->exchange->api()->chart($pairs, "1m", function($api, $symbol, $chart) use ($strategies){
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