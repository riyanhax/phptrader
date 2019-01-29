<?php
use \League\CLImate\CLImate;


class Strategies{
	private $data = [];
	private $climate;
	public $exchange;
	public $symbol;
	private $cacheTable = [];
	private $config = [];
	private $symbolConfig = [];

	public function setData($arv, $symbol){
		//if($this->data) return $this->data;
		$this->data = $this->makeData($arv);
		$this->climate = new CLImate;
		$this->symbol = $symbol;
		$this->loadconfig();
	}
	public function loadconfig(){
		if($this->config) return $this->config;
		$this->config = json_decode(file_get_contents(__DIR__."/config.json"));
		return $this->config;
	}
	public function rsi_adx_bb(){
		
		//$this->getDefaultTrend();
		$price = $this->getPrices();

		$BULL_RSI = $this->rsi($this->config->BULL->rsi);
		$BEAR_RSI = $this->rsi($this->config->BEAR->rsi);
		$RSI14 = $this->rsi(14);

		$maSlow = $this->sma($this->config->SMA->long);
		$maFast = $this->sma($this->config->SMA->short);
		$BBands = $this->bb($this->config->BBands->TimePeriod, $this->config->BBands->NbDevUp, $this->config->BBands->NbDevDn);
		$adx = $this->adx($this->config->ADX->adx);


		$ADX_high = $this->config->ADX->high;
		$ADX_low = $this->config->ADX->low;
		$BEAR_high = $this->config->BEAR->high;
		$BEAR_low = $this->config->BEAR->low;
		$BEAR_MOD_high = $this->config->BEAR->mod_high;
		$BEAR_MOD_low = $this->config->BEAR->mod_low;

		$BULL_high = $this->config->BULL->high;
		$BULL_low = $this->config->BULL->low;
		$BULL_MOD_high = $this->config->BULL->mod_high;
		$BULL_MOD_low = $this->config->BULL->mod_low;


		

		$priceUpperBB = $BBands["lower"] + ($BBands["upper"] - $BBands["lower"]) / 100 * $this->config->BBtrend->upperThreshold;
		$priceLowerBB = $BBands["lower"] + ($BBands["upper"] - $BBands["lower"]) / 100 * $this->config->BBtrend->lowerThreshold;

		if ($price >= $priceUpperBB) $BBtrend_zone = 'high';
		if (($price < $priceUpperBB) && ($price > $priceLowerBB)) $BBtrend_zone = 'middle';
		if ($price <= $priceLowerBB) $BBtrend_zone = 'low';

		$trend = "NO Trend";
		if( $maFast < $maSlow )
		{
			$rsi = $BEAR_RSI;
			$rsi_hi = $BEAR_high;
			$rsi_low = $BEAR_low;

			if( $adx > $ADX_high ){
				$rsi_hi = $rsi_hi + $BEAR_MOD_high;
			}else if( $adx < $ADX_low ){
				$rsi_low = $rsi_low + $BEAR_MOD_low;
			} 
			$trend = "maSlow";

		}else{
			$rsi = $BULL_RSI;
			$rsi_hi = $BULL_high;
			$rsi_low = $BULL_low;
			

			if( $adx > $ADX_high ){
				
				$rsi_hi = $rsi_hi + $BULL_MOD_high;
			}else if( $adx < $ADX_low ){
			 	$rsi_low = $rsi_low + $BULL_MOD_low;
			 	
			}
			$trend = "maFast";
		}

		$action = $this->checkStatus();


		$this->cacheTable = [];

		if( $rsi < $rsi_low && $BBtrend_zone == 'low' && $action !== "wait_sell") {
			
			$action = "BUY";
			$this->cacheTable = [
				"Date" => date("d-m-Y h:i:s"),
				"Symbol" => $this->symbol,
				"Prices" => $price,
				"BULL_RSI" => $BULL_RSI, 
				"BEAR_RSI" => $BEAR_RSI,
				"maSlow" => $maSlow,
				"maFast" => $maFast,
				"BBupper" => $BBands["upper"],
				"BBmiddle" => $BBands["middle"],
				"BBlower" => $BBands["lower"],
				"ADX" => $adx,
				"Action" => $action,
				"Trend" => $trend
			];

			$this->action_buy();

		}else if( $rsi > $rsi_hi && $price >= $priceUpperBB && $action !== "wait_buy") {
			$action = "SELL";
			
			$this->cacheTable = [
				"Date" => date("d-m-Y h:i:s"),
				"Symbol" => $this->symbol,
				"Prices" => $price,
				"BULL_RSI" => $BULL_RSI, 
				"BEAR_RSI" => $BEAR_RSI,
				"maSlow" => $maSlow,
				"maFast" => $maFast,
				"BBupper" => $BBands["upper"],
				"BBmiddle" => $BBands["middle"],
				"BBlower" => $BBands["lower"],
				"ADX" => $adx,
				"Action" => $action,
				"Trend" => $trend
			];
			$this->action_sell();
		}

		$table = [
			[
				"Date" => date("d-m-Y h:i:s"),
				"Symbol" => $this->symbol,
				"Prices" => $price,
				"RSI14"	=> $RSI14,
				"BULL_RSI" => $BULL_RSI, 
				"BEAR_RSI" => $BEAR_RSI,
				"maSlow" => $maSlow,
				"maFast" => $maFast,
				"BBupper" => $BBands["upper"],
				"BBmiddle" => $BBands["middle"],
				"BBlower" => $BBands["lower"],
				"ADX" => $adx,
				"Action" => $action,
				"Trend" => $trend
			]
		];
		
		$this->climate->table($table);
	}

	public function rsi($period=14){

		
        #$data2 = $data;
        #$current = array_pop($data2['close']); #$data['close'][count($data['close']) - 1];    // we assume this is current
        #$prev_close = array_pop($data2['close']); #$data['close'][count($data['close']) - 2]; // prior close
        $rsi = trader_rsi ($this->data['close'], $period);
        $rsi = array_pop($rsi);
        return number_format($rsi,8,".","");

	}

	public function sma($period=14){

        $sma = trader_sma($this->data['close'], $period);
        $sma = (float)@array_pop($sma);
        return number_format($sma,8,".","");

	}


	public function bb($period=20.0, $devup=2.0, $devdn=2.0){

        $bbands = trader_bbands($this->data['close'], $period, $devup, $devdn, 0);
        $upper  = array_pop($bbands[0]);
        $middle = array_pop($bbands[1]); // we'll find a use for you, one day
        $lower  = array_pop($bbands[2]);
        return ["upper" => number_format($upper,8,".",""), "middle" => number_format($middle,8,".",""), "lower" => number_format($lower,8,".","")];
	}

	public function adx($period=3.0){
		$data = $this->data;
		$adx = trader_adx($data['high'], $data['low'], $data['close'], $period);
        if (empty($adx)) {
            return -9;
        }
        $adx = array_pop($adx); #[count($adx) - 1];
        return number_format($adx,8,".","");
	}

	public function setSymbolConfig($arv=[]){
		$this->symbolConfig = $arv;
	}


	private function getPrices(){
		$data = array_pop($this->data["close"]);
		
		return number_format($data,8,".","");
	}


	private function makeData($arv){
		$arvs = [
			"open" => [],
            "high" => [],
            "low" => [],
            "close" => [],
            "volume" => [],
            "openTime" => [],
            "closeTime" => [],
            "assetVolume" => [],
            "baseVolume" => [],
            "trades" => [],
            "assetBuyVolume" => [],
            "takerBuyVolume" => []
            

		];
		foreach ($arv as $key => $value) {
			$arvs["open"][] = $value["open"];
			$arvs["close"][] = $value["close"];
			$arvs["low"][] = $value["low"];
			$arvs["high"][] = $value["high"];
			$arvs["volume"][] = $value["volume"];
			$arvs["trades"][] = isset($value["trades"]) ? $value["trades"] : 0;
		}
		return $arvs;
	}

	private function action_buy(){
		$arvs = $this->getAmount();

		$arv = json_encode(array_merge($arvs, $this->cacheTable));
		file_put_contents(__DIR__."/orders/".$this->symbol.".json",$arv);
	}

	private function action_sell(){
		$arvs = $this->getAmount();
		$sell_order = json_encode(array_merge($arvs, $this->cacheTable));
		$buy_order = file_get_contents(__DIR__."/orders/".$this->symbol.".json");
		$arv_report = json_encode(["buy" => $buy_order, "sell" => $sell_order]);

		file_put_contents(__DIR__."/report/".$this->symbol."-".date("d-m-Y")."-".".json",$arv_report);
		unlink(__DIR__."/orders/".$this->symbol.".json");
	}

	public function test_buy($symbol, $amount){
		
	    //print_r($this->getAmount("buy"));
	    $callJson = $this->getAmount("buy");
	    /*
		Array
		(
		    [symbol] => LUNBTC
		    [orderId] => 34060160
		    [clientOrderId] => OrrtURrf26EBf6U3YhJ4XU
		    [transactTime] => 1548748191321
		    [price] => 0.00047250
		    [origQty] => 21.18000000
		    [executedQty] => 0.00000000
		    [cummulativeQuoteQty] => 0.00000000
		    [status] => NEW
		    [timeInForce] => GTC
		    [type] => LIMIT
		    [side] => BUY
		    [fills] => Array
		        (
		        )

		)

	    */
	    //print_r($callJson["price"]); exit();
		$order = $this->exchange->api()->buy($symbol, $callJson["amount"], $callJson["price"]);
		print_r($order);
	}

	public function getDefaultTrend(){
		
		$data = $this->exchange->getBalances();
		foreach ($data as $key => $value) {
			if($key !== "BTC" && $key !== "USDT"){
				file_put_contents(__DIR__."/orders/".$key."BTC.json",json_encode(["prices" => "", "amount" => number_format(array_sum($value),8,".","")]));
			}
		}
	}

	private function getSymbol(){
		$symbol = substr($this->symbol, -3);
		$symbol2 = substr($this->symbol, -4);
		$gsymbol = "";
		if ($symbol === 'BTC') {
			$gsymbol = str_replace('BTC','', $this->symbol);
		}else if($symbol2 === 'USDT'){
			$gsymbol = str_replace('USDT','', $this->symbol);
		}

		return $gsymbol;
	}

	private function getAmount($type="none"){
		if($type == "none") return 0;
		$symbol = $this->symbol;
		//$config = $this->symbolConfig->{$symbol};
		$config = new stdClass;
		$config->currency = 0.01;
		$this->symbol = "LUNBTC";
		$depth = $this->exchange->api()->depth($this->symbol);

		
	    
	    if($type == "buy"){
	    	$bid = $this->calPrices(array_keys($depth["bids"])[0], 0.4, "buy");//Buy
		    $amount = $config->currency / $bid;
		    return ["amount" => number_format($amount,2,".",""), "price" => number_format($bid,8,".","")];
		}else if($type == "sell"){
			$ask = $this->calPrices(array_keys($depth["asks"])[0], 0.4, "sell");//Sell
			$amount = $this->exchange->getBalance($this->getSymbol())["available"];
			return ["amount" => number_format($amount,2,".",""), "price" => number_format($ask,8,".","")];
		}

	}


	private function calPrices($prices, $profit, $target=""){
		if(!$target) return "";
		if($target == "sell"){
			$price = (($prices * $profit)/100) + $prices;
		}else if($target == "buy"){
			$price = $prices - (($prices * $profit)/100);
		}
		return $price;
	}

	private function checkStatus(){

		if(file_exists(__DIR__."/orders/".$this->symbol.".json")){
			return "wait_sell";
		}else{
			return "wait_buy";
		}
	}
}
?>