<?php

	class ChanneMessege extends Command {

		static private $timeUserMsg = [];

		public function execute(): void
		{
			foreach($this->config['functions_ChanneMessege']['cid'] as $key => $value) {
				$cid = NULL;
				$channelClientList = self::$tsAdmin->getElement('data', self::$tsAdmin->channelClientList($key, '-groups'));
				if(!empty($channelClientList)){
					foreach($channelClientList as $ccl){
						if(!isset(self::$timeUserMsg[$key][$ccl['clid']])){
							self::$tsAdmin->sendMessage(1, $ccl['clid'], $value);
							self::$timeUserMsg[$key][$ccl['clid']] = 1;
						}
						$cid[$ccl['clid']] = 1;
					}
				}
				if(isset(self::$timeUserMsg[$key])){
					if($cid){
						self::$timeUserMsg[$key] = array_intersect_key(self::$timeUserMsg[$key], $cid);
					}else{
						self::$timeUserMsg[$key] = [];
					}
				}
			}
		}
	}

?>