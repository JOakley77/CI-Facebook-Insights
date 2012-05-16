<?php
class Insights {

	public function __construct() {
		// grab an instance of the CI object
		$this->CI =& get_instance();

		// load config
		$this->CI->load->config('insights');

		// set up facebook information
		$this->fb_app_id 			= $this->CI->config->item('app_id');
		$this->fb_app_secret 		= $this->CI->config->item('app_secret');
		$this->fb_app_redirect_uri 	= $this->CI->config->item('app_redirect_uri');

		// set a few config variables
		$this->db_table 			= $this->CI->config->item('db_table_name');
		$this->cache_enabled		= $this->CI->config->item('cache_enabled');
		$this->cache_length			= $this->CI->config->item('cache_length');

		// check to see if we have a database connection
		// if not load the library
		if (!isset($this->CI->db->conn_id)) {
			$this->CI->load->database();
			// now make sure we're connected
			// if not - let's error out
			if (!isset($this->CI->db->conn_id)) {
				show_error('Cannot connect to the database. Must have a database connection to continue.');
			}
		}
	}

	public function authorize_insights() {
		try {
			$this->token 	= $this->get_access_token($this->fb_app_redirect_uri);
		} catch (RedirectionException $re) {
			header('Location: ' . $re->getUrl());
		} catch (FacebookApiException $fe) {
			$message = print_r($fe, TRUE);
			echo $message;
		}

		return $this->token;
	}

	public function get_results() {
		$token 			= $_REQUEST['access_token'];
		$start 			= new DateTime($_REQUEST['date_from']);
		$end 			= new DateTime($_REQUEST['date_until']);

		$insights 		= $this->insights_in_db($start, $end);

		if (!$insights || is_null($insights)) {
			if (isset($_REQUEST['id'])) {
				$id 		= $_REQUEST['id'];
				$insights 	= $this->get_insights_data($token, $id, $start, $end);

				header('Content-Type: text/plain');
				echo "object_id,metric,end_time,period,value\n";

				foreach ($insights['data'] AS $metric) {
					foreach ($metric['values'] AS $row) {
						$split 		= explode('/', $metric['id']);
						$date_str	= explode('T', $row['end_time']);
						$date 		= new DateTime($date_str[0]);
						$date->modify('-1 day');
						$value 		= $row['value'];

						if (is_array($row['value'])) {
							$value = implode(' ', $row['value']);

							echo "{$split[0]},{$metric['name']},{date->format('Y-m-d')},{metric['period']},{$value}\n";
						}
					}
				}
			}
			else {
				$insights = $this->get_index($token);
				$insights = $this->insights_serialize($start, $end, $insights);
				$insights = $this->insights_deserialize($insights);
			}
		}

		return (object) $insights;
	}

	private function get_insights_data($token, $id, $start, $end=NULL) {
		$path 				= $id . '/insights/';
		$url 				= FacebookMethods::getGraphApiUrl($path);
		$params 			= array(
			'access_token'	=> $token,
			'method'		=> 'GET',
			'since'			=> $start->format('Y-m-d')
		);

		if (!is_null($end)) {
			$end = $end->modify('+1 day');
			$end = $end->format('Y-m-d');
		}
		else {
			$end = $start->modify('+1 day');
		}

		$params['until'] 	= $end;
		$insights 			= FacebookMethods::fetchUrl($url, $params);

		return $insights;
	}

	private function get_index($token) {

		if (isset($_REQUEST['date_from'])) {
			$start 	= new DateTime($_REQUEST['date_from']);
		}
		else {
			$start 	= new DateTime(time('Y-m-d'));
		}

		if (isset($_REQUEST['date_until'])) {
			$end 	= new DateTime($_REQUEST['date_until']);
		}
		else {
			$end 	= new DateTime($start->modify('+1 day'));
		}
		
		$params = array();

		return $this->get_insights_data($token, $this->fb_app_id, $start, $end);
	}

	private function insights_in_db($from, $until) {
		// cache is enabled
		if ($this->cache_enabled == TRUE) {
			$sql = $this->CI->db->order_by('cached_at', 'DESC')->where('date_from', $from->format('Y-m-d'))->where('date_until', $until->format('Y-m-d'))->get($this->db_table);

			if ($sql->num_rows() > 0) {
				$now 			= time();
				$row 			= $sql->row();
				$cache_expires 	= strtotime('+' . $this->cache_length, $row->cached_at);

				if ($now <= $cache_expires) {
					$insights 	= $this->insights_deserialize($row);
				}
				else {
					$insights 	= NULL;
				}

				return $insights;
			}
			else {
				return NULL;
			}
		}
		// cache is disabled - return NULL so we can get a fresh copy of insights data
		else {
			return NULL;
		}
	}

	private function get_access_token($base_url) {

		if (isset($_REQUEST['access_token'])) {
			return $_REQUEST['access_token'];
		}

		$params					= array();
		$params['client_id']	= $this->fb_app_id;
		$params['redirect_uri']	= $this->fb_app_redirect_uri;

		if (!isset($_REQUEST['code'])) {
			$params['scope'] 	= 'read_insights';
			$url 				= FacebookMethods::getGraphApiUrl('oauth/authorize', $params);
			throw new RedirectionException($url);
		}
		else {
			$params['client_secret']	= $this->fb_app_secret;
			$params['code']				= $_REQUEST['code'];
			$url 						= FacebookMethods::getGraphApiUrl('oauth/access_token');
			$response					= FacebookMethods::fetchUrl($url, $params);
			$response 					= strstr($response, 'access_token');
			$result 					= substr($response, 13);
			$pos 						= strpos($result, '&');

			if ($pos !== FALSE) {
				$result = substr($result, 0, $pos);
			}
			return $result;
		}
	}

	private function insights_serialize($from, $until, $obj) {
		$data = array(
			'date_from' 		=> $from->format('Y-m-d'),
			'date_until'		=> $until->format('Y-m-d'),
			'serialized_data'	=> serialize($obj)
		);

		// check if cache is enabled
		if ($this->cache_enabled == TRUE) {
			$data['cached_at'] = time();
			if ($this->CI->db->insert($this->db_table, $data)) {
				return (object) $data;
			}
			else {
				return FALSE;
			}
		}
		// cache is diabled - return data object
		else {
			return (object) $data;
		}
	}

	private function insights_deserialize($db_obj) {

		$vars['date_from'] 		= $db_obj->date_from;
		$vars['date_until'] 	= $db_obj->date_until;
		$vars['data'] 			= unserialize($db_obj->serialized_data);
		
		if ($this->cache_enabled == TRUE) {
			$vars['cached_at']		= $db_obj->cached_at;
		}

		return (object) $vars;
	}

	private function get_by_name($obj, $key) {
		$data = NULL;
		foreach ($obj->data AS $row => $obj) {
			foreach ($obj AS $value) {
				if ($value['name'] == $key) {
					$data = $value;
				}
			}
		}

		if (!is_null($data)) {
			$result 		= array();
			$total_value 	= 0;
			$total 			= count($data['values']);

			$result['total_count'] = $total;

			foreach ($data['values'] as $entry) {
				$total++;
				$dt = new DateTime($entry['end_time']);

				$result['values'][] = array(
					'date'			=> $dt->format('m-d-Y'),
					'timestamp'		=> $dt->getTimestamp(),
					'value'			=> $entry['value']
				);

				if (!is_array($entry['value'])) {
					$total_value += $entry['value'];
				}
			}
			//$result['last_value']	= $result['values'][$result['total_count'] - 1];
			$result['total_value'] 	= $total_value;

			return (object) $result;
		}
	}

	private function is_json_object($obj) {
		return (json_decode($obj) != NULL) ? TRUE : FALSE;
	}

}

class RedirectionException extends Exception {
	private $url;

	public function __construct($url) {
		$this->url = $url;
	}

	public function getUrl() {
		return $this->url;
	}
}

class FacebookMethods{

	private static function getUrl($httphost, $path, $params) {
		$url = $httphost;
		if ($path) {
			if ($path[0] === '/') {
				$path = substr($path, 1);
			}
			$url .= $path;
		}
		if ($params) {
			$url .= '?' . http_build_query($params);
		}
		return $url;
	}

	public static function getGraphApiUrl($path = '', $params = array()) {
		return self::getUrl('https://graph.facebook.com/', $path, $params);
	}

	public static function getRestApiUrl($params = array()) {
		return self::getUrl('https://api.facebook.com/', 'restserver.php', $params);
	}

	public static function fetchUrl($url, $params) {
		$params['format'] = 'json-strings';
		$ch 							= curl_init();
		$opts 						= array(
			CURLOPT_CONNECTTIMEOUT 	=> 10,
			CURLOPT_RETURNTRANSFER 	=> true,
			// stupid windows - if you keep getting SSL errors - uncomment the next 3 lines
			// make sure to download FB's PHP-SDK library first and correct the path if needed
			CURLOPT_CAINFO 			=> APPPATH . 'libraries/facebook/fb_ca_chain_bundle.crt',
			CURLOPT_SSL_VERIFYHOST 	=> 0,
			CURLOPT_SSL_VERIFYPEER 	=> 0,
			// end stupid windows
			CURLOPT_TIMEOUT 		=> 60,
			CURLOPT_USERAGENT 		=> 'facebook-php-2.0',
			CURLOPT_URL 			=> $url,
		);
		$opts[CURLOPT_POSTFIELDS] = http_build_query($params, null, '&');
		curl_setopt_array($ch, $opts);
		$result = curl_exec($ch);

		if ($result === false) {
			$e = new Exception(curl_error($ch), curl_errno($ch));
			curl_close($ch);
			throw $e;
		}
		curl_close($ch);

		// check if incoming is a json object
		$json_request = (json_decode($result) != NULL) ? TRUE : FALSE;

		if ($json_request == TRUE) {
			return json_decode($result, true);
		}
		else {
			return $result;
		}
	}
}
