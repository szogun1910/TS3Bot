<?php

	class StatusTwitch extends Command {

		public static $statusTwitch_time = 0;

		public function execute(): void
		{
			if(self::$statusTwitch_time <= time()){
				foreach($this->config['functions_StatusTwitch']['cid_name'] as $cid => $name){
					$jdc = json_decode($this->bot->file_get_contents_curl('https://api.twitch.tv/kraken/streams/'.$name.'?client_id=56o6gfj3nakgeaaqpku3cugkf7lgzk'));
					if(isset($jdc->stream) && $jdc->stream == null){
						$jdc2 = json_decode($this->bot->file_get_contents_curl('https://api.twitch.tv/kraken/users/'.$name.'?client_id=56o6gfj3nakgeaaqpku3cugkf7lgzk'));
						if(isset($jdc2->error)){
							$channel_description = self::$l->sprintf(self::$l->offline_StatusTwitch, $name, $jdc2->logo ?? '');
							$channelinfo = self::$tsAdmin->getElement('data', self::$tsAdmin->channelInfo($cid));
							if($channelinfo['channel_description'] != $channel_description){
								$channelEdit = self::$tsAdmin->channelEdit($cid, [ 'channel_description' => $channel_description ]);
								if(!empty($channelEdit['errors'][0])){
									$this->bot->log(1, 'Kanał o ID:'.$cid.' nie istnieje Funkcja: statusTwitch()');
								}
							}
						}
					}else{
						$channel_description = self::$l->sprintf(self::$l->online_StatusTwitch, $jdc->stream->channel->url, $name, $jdc->stream->game, $jdc->stream->channel->status, $jdc->stream->viewers, $jdc->stream->channel->logo);
						$channelEdit = self::$tsAdmin->channelEdit($cid, [ 'channel_description' => $channel_description ]);
						if(!empty($channelEdit['errors'][0])){
							$this->bot->log(1, 'Kanał o ID:'.$cid.' nie istnieje Funkcja: statusTwitch()');
						}
					}
				}
				self::$statusTwitch_time = time()+60;
			}
		}
	}

?>