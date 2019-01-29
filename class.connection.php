<?php
class Exchange{
	public $api;
	public $balances = [];
	public $test = false;
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

	public function buy($symbol,$amount, $prices){
		$val_btc = $this->getBalance("BTC");
		if(!$this->test && $val_btc > 0.001){
			$data = $this->api()->buy($symbol, $amount, $prices, "LIMIT");
		}
		/*
		Reset Balance
		*/
		$this->getBalances();
		return $data;

	}

	public function sell($symbol,$amount, $prices){
		$val_btc = $this->getBalance($symbol);
		if(!$this->test){
			
		}
		/*
		Reset Balance
		*/
		$this->getBalances();
	}
}
?>