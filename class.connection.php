<?php
class Exchange{
	public $api;
	public $balances = [];
	public $test = true;
	function __construct()
	{
		if(!$this->api){
			$this->api();
		}
	}

	public function api(){
		if($this->api) return $this->api;
		$readKeys = json_decode(file_get_contents(__DIR__."/keys.json"));
		return new Binance\API( $readKeys->key,$readKeys->secret );
	}

	public function getBalances($update=false){
		$balances = $this->api()->balances();
		$arv = [];
		foreach ($balances as $key => $value) {
			if((float)$value["available"] > 0 || (float)$value["onOrder"] > 0){
				$arv[$key] = $value;
			}
		}
		$this->balances = $arv;
		return $this->balances;
	}

	public function getBalance($key){
		if(!$this->balances)$this->getBalances();
		if(isset($this->balances[$key])){
			return $this->balances[$key];
		}else{
			return [
				"available" => 0,
	            "onOrder" => 0,
	            "btcValue" => 0,
	            "btcTotal" => 0
	        ];
		}
	}
	public function getBalanceSymbol($symbol){
		$symbol = str_replace('BTC', '', $symbol);
		if(isset($this->balances[$symbol])){
			return array_sum([$this->balances[$symbol]["available"], $this->balances[$symbol]["onOrder"]]);
		}
		return 0;
	}

	public function getBalanceAvalible($symbol){
		if(!$this->balances)$this->getBalances();
		return $this->balances[$symbol]["available"];
		
	}

	public function buy($symbol,$amount, $prices){
		$val_btc = $this->getBalance("BTC");
		if(!$this->test && $val_btc > 0.001){
			$data = $this->api()->buy($symbol, $amount, $prices, "LIMIT");
		}else{
			$data = array(
			    "symbol" => $symbol,
			    "orderId" => "34060160",
			    "clientOrderId" => "OrrtURrf26EBf6U3YhJ4XU",
			    "transactTime" => time() * 1000,
			    "price" => $prices,
			    "origQty" => $amount,
			    "executedQty" => 0,
			    "cummulativeQuoteQty" => 0,
			    "status" => "NEW",
			    "timeInForce" => "GTC",
			    "type" => "LIMIT",
			    "side" => "BUY",
			    "fills" => []

			);

	    
		}
		/*
		Reset Balance
		*/
		$this->getBalances();
		return $data;

	}

	public function sell($symbol,$amount, $prices){
		$valAmount = $this->getBalance($symbol);
		if(!$this->test && $valAmount >= $amount){
			$data = $this->api()->sell($symbol, $amount, $prices, "LIMIT");
		}else{
			$data = array(
			    "symbol" => $symbol,
			    "orderId" => "34060160",
			    "clientOrderId" => "OrrtURrf26EBf6U3YhJ4XU",
			    "transactTime" => time() * 1000,
			    "price" => $prices,
			    "origQty" => $amount,
			    "executedQty" => 0,
			    "cummulativeQuoteQty" => 0,
			    "status" => "NEW",
			    "timeInForce" => "GTC",
			    "type" => "LIMIT",
			    "side" => "SELL",
			    "fills" => []

			);
		}
		/*
		Reset Balance
		*/
		$this->getBalances();
	}

	public function makeExchangeInfo(){
		$data = $this->api()->exchangeInfo();
		$arv = [];
		foreach ($data["symbols"] as $key => $value) {
			if($value["quoteAsset"] === "BTC"){
				$arv[$value["symbol"]] = [
					"status" => $value["status"],
					"minPrice" => (isset($value["filters"][0]["filterType"]) && $value["filters"][0]["filterType"] == "PRICE_FILTER" ? $value["filters"][0]["minPrice"] : "auto"),
					"tickSize" => (isset($value["filters"][0]["filterType"]) && $value["filters"][0]["filterType"] == "PRICE_FILTER" ? $value["filters"][0]["tickSize"] : "auto")
				];
			}
		}
		file_put_contents(__DIR__."/infoexchange.json", json_encode($arv));
	}
}
?>