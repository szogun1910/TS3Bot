<?php

	class Lvl extends Command {

		public static $lvl_time = 0;
		public static $clientIdle = [];
		
		public function execute(): void
		{
			$lvl_clientlist = [];
			$cos = 0;
			foreach($this->bot->getClientList() as $cl){
				if(!in_array($cl['clid'], $lvl_clientlist)){
					if($cl['client_idle_time'] <= 700){
						if(isset(self::$clientIdle[$cl['client_database_id']])){
							self::$clientIdle[$cl['client_database_id']]++;
						}else{
							self::$clientIdle[$cl['client_database_id']] = 0;
						}
					}else{
						self::$clientIdle[$cl['client_database_id']] = 0;
					}
					if($cl['client_idle_time'] <= 1000){
						if(self::$clientIdle[$cl['client_database_id']] >= 300){
							$exp = 0.047;
							if($cl['client_idle_time'] == 0){
								$idle = 1;
							}else{
								$idle = $cl['client_idle_time'];
							}
							$cit = round(pow($idle, 1/1.89), 10);
							$expup = $exp/$cit;
						}else{
							$expup = 0.024;
						}
					}else{
						$exp = 0.047;
						$cit = round(pow($cl['client_idle_time']/1000, 1/1.89), 10);
						$expup = $exp/$cit;
					}
					try {
						$count = 0;
						$prepare = Bot::$db->prepare("UPDATE `users` SET `exp` = exp+:exp WHERE `cui` = :cui");
						$prepare->bindValue(':exp', $expup, PDO::PARAM_STR);
						$prepare->bindValue(':cui', $cl['client_unique_identifier'], PDO::PARAM_STR);
						$prepare->execute();
						$prepare = Bot::$db->prepare("SELECT COUNT(id) AS `count`, `exp`, `lvl` FROM `users` WHERE `cui` = :cui GROUP BY `id`");
						$prepare->bindValue(':cui', $cl['client_unique_identifier'], PDO::PARAM_STR);
						$prepare->execute();
						while($row = $prepare->fetch()){
							$count = $row['count'];
							$lvl = $row['lvl'];
							$exp = $row['exp'];
						}
						if($count != 0){
							$nextLvl = $lvl+1;
							$downlvl = $lvl-1;
							if($this->config['functions_Lvl']['group'] == true){
								if(array_intersect(explode(',', $cl['client_servergroups']), $this->config['functions_Lvl']['required_group'])){
									if(!in_array($this->config['functions_Lvl']['lvl'][$lvl]['gid'], explode(',', $cl['client_servergroups']))){
										$serverGroupAddClient = self::$tsAdmin->serverGroupAddClient($this->config['functions_Lvl']['lvl'][$lvl]['gid'], $cl['client_database_id']);
										if(!empty($serverGroupAddClient['errors'][0])){
											$this->bot->log(1, 'Grupa o ID:'.$this->config['functions_Lvl']['lvl'][$lvl]['gid'].' nie istnieje Funkcja: Lvl()');
										}
									}
									if($lvl != 1){
										if(in_array($this->config['functions_Lvl']['lvl'][$downlvl]['gid'], explode(',', $cl['client_servergroups']))){
											self::$tsAdmin->serverGroupDeleteClient($this->config['functions_Lvl']['lvl'][$downlvl]['gid'], $cl['client_database_id']);
										}
									}
								}
							}
							if($this->config['functions_Lvl']['lvl'][$nextLvl]['exp'] <= $exp){
								if($cl['client_type'] == 0) {
									$prepare = Bot::$db->prepare("UPDATE `users` SET `lvl` = :lvl WHERE `cui` = :cui");
									$prepare->bindValue(':lvl', $nextLvl, PDO::PARAM_INT);
									$prepare->bindValue(':cui', $cl['client_unique_identifier'], PDO::PARAM_STR);
									$prepare->execute();
									self::$tsAdmin->sendMessage(1, $cl['clid'], $this->config['functions_Lvl']['lvl'][$nextLvl]['text']);
								}
							}
						}
					} catch (PDOException $e) {
						$this->bot->log(1, $e->getMessage());
					}
					$lvl_clientlist[] = $cl['clid'];
				}
			}
			if($this->config['functions_Lvl']['top_list'] && self::$lvl_time <= time()){
				if(!empty($this->config['functions_Lvl']['cldbid'])){
					$cldbid = implode(",", $this->config['functions_Lvl']['cldbid']);
				}
				$top = NULL;
				$s = 0;
				try {
					$query2 = self::$db->query("SELECT `client_nickname`, `cui`, `cldbid`, `lvl`, `exp`, `gid` FROM `users` WHERE `cldbid` NOT IN({$cldbid}) ORDER BY `exp` DESC");
					while($row2 = $query2->fetch()){
						if(!array_intersect(explode(',', $row2['gid']), $this->config['functions_Lvl']['gid'])){
							$s++;
							$lvl2 = $row2['lvl'];
							$nextlvl2 = $lvl2+1;
							$expnext = $this->config['functions_Lvl']['lvl'][$nextlvl2]['exp']-$this->config['functions_Lvl']['lvl'][$row2['lvl']]['exp'];
							$exp = round($row2['exp']-$this->config['functions_Lvl']['lvl'][$row2['lvl']]['exp'], 2);
							$nick = $this->bot->getUrlName($row2['cldbid'], $row2['cui'], $row2['client_nickname']);
							$top .= self::$l->sprintf(self::$l->row_Lvl, $s, $nick, $row2['lvl'], '('.$exp.'/'.$expnext.')');
						}
						if($s >= $this->config['functions_Lvl']['limit']){
							break;
						}
					}
					$channelEdit= self::$tsAdmin->channelEdit($this->config['functions_Lvl']['cid'], ['channel_description' => self::$l->sprintf(self::$l->list_Lvl, $top)]);
					if(!empty($channelEdit['errors'][0]) && $channelEdit['errors'][0] != 'ErrorID: 771 | Message: channel name is already in use'){
						$this->bot->log(1, 'Kanał o ID:'.$this->config['functions_Lvl']['cid'].' nie istnieje Funkcja: Lvl()');
					}
					self::$lvl_time = time()+60;
				} catch (PDOException $e) {
					$this->bot->log(1, $e->getMessage());
				}
			}

		}
	}

?>