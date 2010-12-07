<?php

$data = $this->module_data('quote');
var_dump($data);
if(isset($this->ex[4]) && $this->ex[4]==='add')
{
	if(isset($this->ex[5]))
	{
		// Combine everything after the fourth segment into a string
		$combined = $this->ex;
		for($i=0;$i<5;$i++)
		{
			unset($combined[$i]);
		}
		if( ! isset($data[$channel])) $data[$channel] = array('last_id'=>0,'quotes'=>array());
		$id = $data[$channel]['last_id'] +1;
		$data[$channel]['last_id'] = $id;
		$data[$channel]['quotes'][$id] = array('id'=>$id, 'quote'=>implode(' ',$combined), 'nick'=>$messenger);
		$this->send_data('NOTICE', $messenger . ' :Quote added with id #' . $id . '.');
		$this->module_data('quote',$data);
	}
	else
	{
		$this->send_data('NOTICE', $messenger . ' :You need to provide the actual quote.');
	}
}
elseif(isset($this->ex[4]) && is_numeric($this->ex[4]))
{
	if(isset($data[$channel]['quotes'][$this->ex[4]]))
	{
		$message = ' :Quote: ' . $data[$channel]['quotes'][$this->ex[4]]['quote'] . ' (#'.$this->ex[4].' by '.$data[$channel]['quotes'][$this->ex[4]]['nick'].').';
		if(preg_match('/^#/', $channel))
		{
			$this->send_data('PRIVMSG', $channel . $message);
		}
		else
		{
			$this->send_data('NOTICE', $messenger . $message);
		}
	}
	else
	{
		$this->send_data('NOTICE', $messenger . ' :No such quote.');
	}
}
else
{
	print_r($data[$channel]);
	$id = rand(1,$data[$channel]['last_id']);
	while( ! isset($data[$channel]) && ! empty($data[$channel]['quotes']))
	{
		$id = rand(1,$data[$channel]['last_id']);
	}
	$message = ' :Quote: ' . $data[$channel]['quotes'][$id]['quote'] . ' (#'.$id.' by '.$data[$channel]['quotes'][$id]['nick'].').';
	if(preg_match('/^#/', $channel))
	{
		$this->send_data('PRIVMSG', $channel . $message);
	}
	else
	{
		$this->send_data('NOTICE', $messenger . $message);
	}
}

// EOF
