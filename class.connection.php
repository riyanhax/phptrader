<?php
class Exchange{
	public $api;
	public $balances = [];
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

	public function getBalances(){
		$balances = $this->api()->balances($ticker);
		$arv = [];
		foreach ($balances as $key => $value) {
			if((float)$value["available"] > 0 || (float)$value["onOrder"] > 0){
				$arv[$key] = $value;
			}
		}
		$this->balances = $arv;
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
}
?>