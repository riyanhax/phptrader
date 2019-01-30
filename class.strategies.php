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
	public $pairInfo = "";

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


		

		if( $rsi < $rsi_low && $BBtrend_zone == 'low') {// && $action !== "wait_sell"

			$this->action_buy();

		}else if( $rsi > $rsi_hi && $price >= $priceUpperBB) {// && $action !== "wait_buy"
			
			$this->action_sell();
		}
		if($action === "wait_sell"){
			$cache_amount = $this->exchange->getBalanceSymbol($this->symbol);
		}else{
			$cache_amount = $this->exchange->getBalanceSymbol($this->symbol) - $this->symbolConfig->{$this->symbol}->asset;
		}
		

		$table = [
			[
				"Date" => date("d-m-Y h:i:s"),
				"Symbol" => $this->symbol,
				"Prices" => $price,
				"Amount" => number_format($cache_amount,2,".",""),
				"SumBTC" => number_format(($cache_amount) * $price,8,".",""),
				"BULL_RSI" => $BULL_RSI, 
				"BEAR_RSI" => $BEAR_RSI,
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
		$callJson = $this->getAmount("buy");
		$order = $this->exchange->buy($this->symbol, $callJson["amount"], $callJson["price"]);
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

		if(is_array($order)){
			file_put_contents(__DIR__."/orders/".$this->symbol.".json",json_encode($order));
		}
	}

	private function action_sell(){
		$callJson = $this->getAmount("sell");

		if(is_array($callJson) && $callJson["amount"] > 0 && $callJson["price"] > 0){

			$order = $this->exchange->sell($this->symbol, $callJson["amount"], $callJson["price"]);

			if($order){
				$sell_order = json_encode($order);
				$buy_order = file_get_contents(__DIR__."/orders/".$this->symbol.".json");
				$arv_report = json_encode(["buy" => $buy_order, "sell" => $sell_order]);
				file_put_contents(__DIR__."/report/".$this->symbol."-".date("d-m-Y")."-".".json",$arv_report);

				unlink(__DIR__."/orders/".$this->symbol.".json");
			}
		}
		
	}

	public function test_buy($symbol, $amount){
		
	    //print_r($this->getAmount("buy"));
	    $callJson = $this->getAmount("sell");
	    //print_r($this->exchange->api()->exchangeInfo());
	    //print_r($callJson["price"]); exit();
		//$order = $this->exchange->api()->sell($symbol, $callJson["amount"], $callJson["price"]);
		//print_r($order);
	}

	public function getDefaultTrend(){
		
		$data = $this->exchange->getBalances();
		foreach ($data as $key => $value) {
			if($key !== "BTC" && $key !== "USDT" && isset($this->symbolConfig->{$key."BTC"})){
				file_put_contents(__DIR__."/orders/".$key."BTC.json",json_encode(["prices" => "", "amount" => number_format(array_sum($value),8,".","")]));
			}
		}
	}

	private function getSymbol(){
		return str_replace('BTC','', $this->symbol);
	}

	private function getAmount($type="none"){
		if($type !== "buy" || $type !== "sell" ) return 0;
		
		//$this->symbol = "LUNBTC";
		$symbol = $this->symbol;
		//$config = $this->symbolConfig->{$symbol};
		//$config = new stdClass;

		$depth = $this->exchange->api()->depth($this->symbol);

		
	    
	    if($type == "buy"){
	    	$bid = $this->calPrices(array_keys($depth["bids"])[0], "buy");//Buy

	    	$readAmount = $this->exchange->getBalance($this->getSymbol());

	    	$asset_fix_amount = $this->symbolConfig->{$this->symbol}->asset;
	    	$cal_amount = ($config->currency / $bid);
	    	
	    	if($cal_amount >= $asset_fix_amount) $cal_amount = $asset_fix_amount;

		    $amount2 = array_sum([$readAmount["available"],$readAmount["onOrder"]]);

		    $amount = $cal_amount - $amount2;
		    

		    return ["amount" => number_format($amount,2,".",""), "price" => number_format($bid,8,".","")];

		}else if($type == "sell"){
			
			$ask = $this->calPrices(array_keys($depth["asks"])[0], "sell");//Sell
			$amount = $this->exchange->getBalance($this->getSymbol())["available"];

			return ["amount" => number_format($amount,2,".",""), "price" => number_format($ask,8,".","")];
		}

	}


	private function calPrices($prices, $target=""){
		if(!$target) return "";
		$getIngo = $this->pairInfo->{$this->symbol};
	    $calProfit = $this->symbolConfig->{$this->symbol};

	    if(isset($this->pairInfo->{$this->symbol}) && isset($getIngo->tickSize)){
	    	

	    	if($target == "sell"){
		    	$price = ($getIngo->tickSize * $calProfit->updownprices) + $prices;
		    }else if($target == "buy"){
		    	$price = $prices - ($getIngo->tickSize * $calProfit->updownprices);
		    }

	    }else{

	    	if($calProfit->updownprices > 0.2) $calProfit->updownprices = 0.2;// fix updown

	    	if($target == "sell"){
				$price = (($prices * $calProfit->updownprices)/100) + $prices;
			}else if($target == "buy"){
				$price = $prices - (($prices * $calProfit->updownprices)/100);
			}
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