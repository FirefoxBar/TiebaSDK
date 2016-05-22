<?php

/**
 * 吧相关
 * 
 * @author ShuangYa
 * @package TiebaSDK
 * @link http://www.sylingd.com/
 * @copyright Copyright (c) 2015 ShuangYa
 * @license http://lab.sylingd.com/go.php?name=framework&type=license
 */

class TiebaForum {
	/**
	 * 获取单个贴吧的信息
	 * @access public
	 * @param string $kw 贴吧名称
	 * @return array
	 */
	public static function getInfo($kw) {
		$data = [
			'_client_id' => TiebaCommon::getClient('_client_id'),
			'_client_type' => TiebaCommon::getClient('_client_type'),
			'_client_version' => TiebaCommon::getClient('_client_version'),
			'_phone_imei' => TiebaCommon::getClient('_phone_imei'),
			'from' => 'tieba',
			'kw' => $kw,
			'pn' => '1',
			'q_type' => '2',
			'rn' => '30',
			'with_group' => '1'
		];
		$data['sign'] = TiebaCommon::clientSign($data);
		$url = TiebaCommon::createUrl('c/f/frs/page');
		$info = json_decode(TiebaCommon::fetchUrl($url, ['post' => $data]), 1);
		if (!$info) {
			throw new TiebaException('Network error');
		}
		return $info;
	}
	/**
	 * 获取贴吧的fid
	 * @access public
	 * @param string $kw 贴吧名称
	 * @return int
	 */
	public static function getFid($kw) {
		static $fid = [];
		if (!isset($fid[md5($kw)])) {
			$tb = self::getInfo($kw);
			$fid[md5($kw)] = $tb['forum']['id'];
		}
		return $fid[md5($kw)];
	}
	/**
	 * 获取某贴吧的贴子列表
	 * @access public
	 * @param string $kw 贴吧名称
	 * @param int $page 页码
	 * @return array
	 */
	public static function getThreadList($kw, $page = 1) {
		$data = [
			'_client_id' => TiebaCommon::getClient('_client_id'),
			'_client_type' => TiebaCommon::getClient('_client_type'),
			'_client_version' => TiebaCommon::getClient('_client_version'),
			'_phone_imei' => TiebaCommon::getClient('_phone_imei'),
			'from' => 'tieba',
			'kw' => $kw,
			'pn' => $page,
			'q_type' => '2',
			'rn' => '30',
			'with_group' => '1'
		];
		$data['sign'] = TiebaCommon::clientSign($data);
		$url = TiebaCommon::createUrl('c/f/frs/page');
		$info = json_decode(TiebaCommon::fetchUrl($url, ['post' => $data]), 1);
		if (!$info) {
			throw new TiebaException('Network error');
		}
		return $info['thread_list'];
	}
	/**
	 * 获取贴子内容
	 * @access public
	 * @param int $kz 贴子ID
	 * @param int $page 页码，当倒序时此参数无效
	 * @return array
	 */
	public static function getThread($kz, $page = 1, $last = FALSE) {
		$data = [
			'_client_id' => TiebaCommon::getClient('_client_id'),
			'_client_type' => TiebaCommon::getClient('_client_type'),
			'_client_version' => TiebaCommon::getClient('_client_version'),
			'_phone_imei' => TiebaCommon::getClient('_phone_imei'),
			'back' => 0,
			'kz' => $kz
		];
		if ($last) {
			$data['last'] = 1;
			$data['net_type'] = TiebaCommon::getClient('net_type');
			$data['q_type'] = '2';
			$data['r'] = 1;
		} else {
			$data['net_type'] = TiebaCommon::getClient('net_type');
			$data['pn'] = $page;
			$data['q_type'] = '2';
		}
		$data['rn'] = '30';
		$data['timestamp'] = TiebaCommon::getTimestamp();
		$data['with_floor'] = '1';
		$data['sign'] = TiebaCommon::clientSign($data);
		$url = TiebaCommon::createUrl('c/f/pb/page');
		$info = json_decode(TiebaCommon::fetchUrl($url, ['post' => $data]), 1);
		if (!$info) {
			throw new TiebaException('Network error');
		}
		return $info;
	}
}