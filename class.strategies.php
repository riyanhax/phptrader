<?php
use \League\CLImate\CLImate;
class Strategies{
	private $data = [];
	private $climate;
	public $exchange;
	private $symbol;
	public function setData($arv, $symbol){
		//if($this->data) return $this->data;
		$this->data = $this->makeData($arv);
		$this->climate = new CLImate;
		$this->symbol = $symbol;
	}
	public function rsi_adx_bb(){
		
		$price = $this->getPrices();

		$BULL_RSI = $this->rsi(14.6);
		$BEAR_RSI = $this->rsi(10.5);
		$maSlow = $this->sma(1000.0);
		$maFast = $this->sma(50.0);
		$BBands = $this->bb(20.0, 2.0, 2.0);
		$adx = $this->adx(3.0);
		$ADX_high = 70.0;
		$ADX_low = 50.0;
		$BEAR_high = 60.4;
		$BEAR_low = 28.2;
		$BEAR_MOD_high = 1.4;
		$BEAR_MOD_low = -1.5;

		$BULL_high = 85.6;
		$BULL_low = 42.3;
		$BULL_MOD_high = 3.2;
		$BULL_MOD_low = -9;


		$BBtrend = ["upperThreshold" => 50, "lowerThreshold" => 50];

		$priceUpperBB = $BBands["lower"] + ($BBands["upper"] - $BBands["lower"]) / 100 * $BBtrend["upperThreshold"];
		$priceLowerBB = $BBands["lower"] + ($BBands["upper"] - $BBands["lower"]) / 100 * $BBtrend["lowerThreshold"];

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

		$action = "Wait";
		if( $rsi < $rsi_low && $BBtrend_zone == 'low' ) {
			
			$action = "BUY";
			$this->action_buy();
		}else if( $rsi > $rsi_hi && $price >= $priceUpperBB) {
			$action = "SELL";
			$this->action_sell();
		}

		$table = [
			[
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
		
		$arv = json_encode(["prices" => $this->getPrices(), "amount" => "", "symbol" => $this->symbol]);
		file_put_contents(__DIR__."/orders/BUY-".$this->symbol.".json",$arv);
	}

	private function action_sell(){
		$arv = json_encode(["prices" => $this->getPrices(), "amount" => "", "symbol" => $this->symbol]);
		file_put_contents(__DIR__."/orders/SELL-".$this->symbol.".json",$arv);
	}
}
?>