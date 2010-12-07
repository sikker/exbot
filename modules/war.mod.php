<?php

if(isset($this->ex[4]))
{
	$data = $this->module_data('war');
	$message = FALSE;
	$notice = FALSE;
	$chan = $channel;
	switch($this->ex[4])
	{
		case 'start':
			if( ! isset($this->ex[5]))
			{
				$notice = 'No delay parameter given. Must be !war start DELAY DURATION.';
			}
			elseif( ! isset($this->ex[6]))
			{
				$notice = 'No duration parameter given. Must be !war start DELAY DURATION.';
			}
			else
			{
				$delay = $this->ex[5];
				$duration = $this->ex[6];
				if( ! isset($data['wars'])) $data['wars'] = array();
				if( ! isset($data['wars'][$chan])) $data['wars'][$chan] = array();
				$name = $data['names'][ rand(0,count($data['names'])-1) ];
				while(isset($data['wars'][$chan][$name]))
				{
					$name = $data['names'][ rand(0,count($data['names'])-1) ];
				}
				$data['wars'][$chan][$name] = array(
					'name' => $name,
					'instigator' => $messenger,
					'spawned_time' => time(),
					'start_time' => time() + ($delay*60),
					'end_time' => time() + ($delay*60) + ($duration*60),
					'participants' => array(),
				);
				$message = 'Word war ' . $name . ' will start in ' . $delay . ' ' . ($delay!==1 ? 'minutes' : 'minute') . ' and will last for ' . $duration . ' ' . ($duration!==1 ? 'minutes' : 'minute') . '.';
			}
			break;
		case 'join':
			if( ! isset($this->ex[5]) || $this->ex[5] === 'last')
			{
				if(empty($data['wars']) || ! isset($data['wars'][$chan]) || empty($data['wars'][$chan]))
				{
					$notice = 'No ongoing word wars.';
				}
				else
				{
					$intermediate_timestamp = 0;
					$latest = '';
					foreach($data['wars'][$chan] as $war)
					{
						if($war['spawned_time']>$intermediate_timestamp) $latest = $war['name'];
						$intermediate_timestamp = $war['spawned_time'];
					}
					$data['wars'][$chan][$latest]['participants'][$messenger] = $messenger;
					$notice = 'You have joined word war ' . $data['wars'][$chan][$latest]['name'] . '.';
				}
			}
			else
			{
				if( ! isset($data['wars'][$chan]) )
				{
					$notice = 'No ongoing word wars.';
				}
				elseif( ! isset($data['wars'][$chan][ $this->ex[5] ]))
				{
					$notice = 'No such word war.';
				}
				else
				{
					$data['wars'][$chan][$this->ex[5]]['participants'][] = $messenger;
					$notice = 'You have joined word war ' . $this->ex[5] . '.';
				}
			}
			break;
		case 'leave':
			if( ! isset($this->ex[5]))
			{
				$notice = 'No word war specified.';
			}
			else
			{
				if( ! isset($data['wars'][$chan]))
				{
					$notice = 'No ongoing word wars.';	
				}
				elseif( ! isset($data['wars'][$chan][$this->ex[5]]))
				{
					$notice = 'No such word war.';
				}
				elseif( ! isset($messenger, $data['wars'][$chan][$this->ex[5]]['participants'][$messenger]))
				{
					$notice = 'You are not in that word war.';
				}
				else
				{
					unset($data['wars'][$chan][$this->ex[5]]['participants'][$messenger]);
					$notice = 'You have left word war ' . $data['wars'][$chan][$this->ex[5]]['name'] . '.';
				}
			}
			break;
		case 'stop':
			if( ! isset($this->ex[5]))
			{
				$notice = 'No word war specified.';
			}
			else
			{
				if( ! isset($data['wars'][$chan]))
				{
					$notice = 'No ongoing word wars.';
				}
				elseif( ! isset($data['wars'][$chan][$this->ex[5]]))
				{
					$notice = 'No such word war.';
				}
				elseif( ! $this->authenticate($messenger) && $data['wars'][$chan][$this->ex[5]]['instigator'] !== $messenger)
				{
					$notice = 'You are not permitted to stop this word war.';
				}
				else
				{
					unset($data['wars'][$chan][$this->ex[5]]);
					$message = 'Word war ' . $this->ex[5] . ' stopped by ' . $messenger  . '.';
				}
			}
			break;
		case 'name':
			if( ! isset($this->ex[5]) || $this->ex[5] === 'last')
			{
				$notice = 'No name given.';
			}
			else
			{
				$data['names'][] = $this->ex[5];
				$notice = 'Name added.';
			}
			break;
		case 'status':
			$active = FALSE;
			foreach($data['wars'][$chan] as $war)
			{
				$name = $war['name'];
				$start = $war['start_time'];
				$end = $war['end_time'];
				if($start>time())
				{
					$active = TRUE;
					$start_minutes = round(($start - time())/60, 2);
					$end_minutes = round(($end - $start)/60, 2);
					$this->send_data('NOTICE', $messenger . " :Word war $name will BEGIN in $start_minutes " . ($start_minutes!==1 ? 'minutes' : 'minute') . " and will last for $end_minutes " .  ($end_minutes!==1 ? 'minutes' : 'minute') . ".");
				}
				elseif($end>time())
				{
					$active = TRUE;
					$end_minutes = round(($end-time())/60, 2);
					$this->send_data('NOTICE', $messenger . " :Word war $name will END in $end_minutes " . ($end_minutes!==1 ? 'minutes' : 'minute') . ".");
				}
			}
			if(empty($data['wars'][$chan]) || $active===FALSE)
			{
				$notice = "No ongoing word wars.";
			}
	}
	$this->module_data('war', $data);
	if($notice) $this->send_data('NOTICE', $messenger . ' :' . $notice);
	if($message) $this->send_data('PRIVMSG', $chan . ' :' . $message);
}

// EOF
