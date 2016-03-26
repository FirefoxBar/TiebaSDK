<?php

/**
 * 常用类
 * 
 * @author ShuangYa
 * @package TiebaSDK
 * @link http://www.sylingd.com/
 * @copyright Copyright (c) 2015 ShuangYa
 * @license http://lab.sylingd.com/go.php?name=framework&type=license
 */

class TiebaCommon {
	protected $client = [
		'typeName' => 'Windows8',
		'typeId' => 4,
		'version' => '1.2.1.17',
		'net_type' => 3
	];
	/**
	 * 进行CURL请求
	 */
	public static function fetchUrl($url, $data) {
		$ch = curl_init($url);
		//header
		if (!isset($data['UA'])) {
			$ua = 'bdtb for ' . self::$client['typeName'] . ' ' . self::$client['version'];
		} else {
			$ua = 'Firefox 39.0 Mozilla/5.0 (Windows NT 6.3; rv:39.0) Gecko/20100101 Firefox/39.0';
		}
		$header = ['User-Agent: ' . $ua];
		if (isset($data['header'])) {
			$header = @array_merge($header, $data['header']);
		}
		curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
		//Cookie
		if (isset($data['cookie'])) {
			curl_setopt($ch, CURLOPT_COOKIE, $data['cookie']);
		}
		if (isset($data['post'])) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $data['post']);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$r = curl_exec($ch);
		@curl_close($ch);
		return $r;
	}
	public static function randStr ($length) {
		$str = 'abcdefghijklimnopqrstuvwxyz0123456789';
		$str = str_shuffle($str);
		$str = substr($str, 0, $length);
		return $str; 
	}
	/**
	 * 创建贴吧URL
	 * @access public
	 * @param string $path
	 * @param string $prefix 域名前缀
	 * @return string
	 */
	public static function createUrl ($path, $prefix = 'c') {
		$tburl = 'tieba.baidu.com';
		$r = 'http://';
		if (!empty($prefix)) {
			$r .= $prefix . '.';
		}
		$r .= $tburl . '/' . $path;
		return $r;
	}
	/**
	 * 生成客户端签名
	 * @access public
	 * @param array $data POST数据
	 * @return string
	 */
	public static function clientSign($data) {
		$sign_str = '';
		foreach ($data as $k => $v) {
			$sign_str .= $k . '=' . $v;
		}
		$sign = strtoupper(md5($sign_str . 'tiebaclient!!!'));
		return $sign;
	}
	/**
	 * 获取tbs
	 * @access public
	 * @param string $BDUSS BDUSS
	 * @param boolean $focus_refresh 是否强制刷新
	 * @return string
	 */
	public static function getTbs($BDUSS, $focus_refresh = FALSE) {
		static $tbs = [];
		$k = md5($BDUSS);
		if (isset($tbs[$k]) && !$focus_refresh) {
			return $tbs[$k];
		}
		$url = self::createUrl('dc/common/tbs', '');
		$tbs_result = json_decode(self::fetchUrl($url), 1);
		if (!$tbs_result) {
			throw new TiebaException('Network error');
		}
		$tbs[$k] = $tbs_result['tbs'];
		return $tbs[$k];
	}
	/**
	 * 生成客户端信息
	 * @access public
	 * @param string $k
	 * @return string
	 */
	public static function getClient($k) {
		static $client = NULL;
		if ($client === NULL) {
			$client_id = [];
			for ($i=1; $i<=28; $i++) {
				$client_id[] = strtoupper(self::randStr(2));
			}
			$client_id = implode('-', $client_id);
			$client = [
				'_client_id' => $client_id,
				'_client_type' => self::$client['typeId'],
				'_client_version' => self::$client['version'],
				'_phone_imei' => md5(self::randStr(6)),
				'net_type' => self::$client['net_type']
			];
		}
		return (isset($client[$k]) ? $client[$k] : '');
	}
}