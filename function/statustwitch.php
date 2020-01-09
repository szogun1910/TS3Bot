<?php

	class StatusTwitch extends Command {

		public static $statusTwitch_time = 0;
		public static $statusTwitch_online = [];

		public function execute(): void
		{
			if(self::$statusTwitch_time <= time()){
				foreach($this->config['functions_StatusTwitch']['cid_name'] as $cid => $value){
					$ch = curl_init();
					curl_setopt_array($ch, [
						CURLOPT_HTTPHEADER => [
							'Accept: application/vnd.twitchtv.v5+json',
							'Client-ID: 56o6gfj3nakgeaaqpku3cugkf7lgzk'
						],
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_URL => 'https://api.twitch.tv/helix/users?login='.$value['users']
					]);
					$userId = json_decode(curl_exec($ch), true)['data'][0]['id'];
					if(empty(self::$statusTwitch_online[$userId])){
						self::$statusTwitch_online[$userId] = 0;
					}
					$ch = curl_init();
					curl_setopt_array($ch, [
						CURLOPT_HTTPHEADER => [
							'Accept: application/vnd.twitchtv.v5+json',
							'Client-ID: 56o6gfj3nakgeaaqpku3cugkf7lgzk'
						],
						CURLOPT_RETURNTRANSFER => true,
						CURLOPT_URL => 'https://api.twitch.tv/kraken/streams/'.$userId.'?stream_type=live'
					]);
					$jdc = json_decode(curl_exec($ch));
					if(empty($jdc->stream)){
						if(self::$statusTwitch_online[$userId] == 1){
							self::$statusTwitch_online[$userId] = 0;
						}
						$ch = curl_init();
						curl_setopt_array($ch, [
							CURLOPT_HTTPHEADER => [
								'Accept: application/vnd.twitchtv.v5+json',
								'Client-ID: 56o6gfj3nakgeaaqpku3cugkf7lgzk'
							],
							CURLOPT_RETURNTRANSFER => true,
							CURLOPT_URL => 'https://api.twitch.tv/kraken/channels/'.$userId
						]);
						$jdc = json_decode(curl_exec($ch));
						$statusTwitch_name = self::$l->sprintf($value['channel_name'], '[Offline]');
						$channel_description = self::$l->sprintf(self::$l->offline_StatusTwitch, $value['users'], $jdc->logo ?? '');
						$channelinfo = self::$tsAdmin->getElement('data', self::$tsAdmin->channelInfo($cid));
						if($channelinfo['channel_description'] != $channel_description){
							$channelEdit = self::$tsAdmin->channelEdit($cid, [ 'channel_description' => $channel_description, 'channel_name' => $statusTwitch_name ]);
							if(!empty($channelEdit['errors'][0]) && $channelEdit['errors'][0] != 'ErrorID: 771 | Message: channel name is already in use'){
								$this->bot->log(1, 'Kanał o ID:'.$cid.' nie istnieje Funkcja: statusTwitch()');
							}
						}
					}else{
						if(self::$statusTwitch_online[$userId] == 0 && $value['info'] == true){
							self::$statusTwitch_online[$userId] = 1;
							foreach($this->bot->getClientList() as $cl) {
								self::$tsAdmin->sendMessage(1, $cl['clid'], self::$l->sprintf($value['info_text'], $value['users'], $jdc->stream->channel->url));
							}
						}
						$statusTwitch_name = self::$l->sprintf($value['channel_name'], '[Online]');
						$channel_description = self::$l->sprintf(self::$l->online_StatusTwitch, $jdc->stream->channel->url, $value['users'], $jdc->stream->game, $jdc->stream->channel->status, $jdc->stream->viewers, $jdc->stream->channel->logo);
						$channelEdit = self::$tsAdmin->channelEdit($cid, [ 'channel_description' => $channel_description, 'channel_name' => $statusTwitch_name ]);
						if(!empty($channelEdit['errors'][0]) && $channelEdit['errors'][0] != 'ErrorID: 771 | Message: channel name is already in use'){
							$this->bot->log(1, 'Kanał o ID:'.$cid.' nie istnieje Funkcja: statusTwitch()');
						}
					}
				}
				self::$statusTwitch_time = time()+60;
			}
		}
	}

?>