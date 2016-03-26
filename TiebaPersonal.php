<?php

/**
 * 个人相关
 * 
 * @author ShuangYa
 * @package TiebaSDK
 * @link http://www.sylingd.com/
 * @copyright Copyright (c) 2015 ShuangYa
 * @license http://lab.sylingd.com/go.php?name=framework&type=license
 */

class TiebaPersonal {
	/**
	 * 获取我喜欢的贴吧
	 * @access public
	 * @param string $BDUSS
	 * @return array
	 */
	public static function getMyLike($BDUSS) {
		$data = [
			'BDUSS' => $BDUSS,
			'_client_id' => TiebaCommon::getClient('_client_id'),
			'_client_type' => TiebaCommon::getClient('_client_type'),
			'_client_version' => TiebaCommon::getClient('_client_version'),
			'_phone_imei' => TiebaCommon::getClient('_phone_imei'),
			'from' => 'tieba',
			'net_type' => TiebaCommon::getClient('net_type')
		];
		$data['sign'] = TiebaCommon::clientSign($sign);
		$url = TiebaCommon::createUrl('c/f/forum/like');
		$info = json_decode(TiebaCommon::fetchUrl($url, ['post' => $data]), 1);
		if (!$info) {
			throw new TiebaException('Network error');
		}
		return $info;
	}
	/**
	 * 签到
	 * @access public
	 * @param string $BDUSS
	 * @param string $tieba 贴吧名称
	 * @param string $fid 贴吧ID
	 * @return array 成功为[1, '经验值']失败为[0, 原因（转化后）, 原因（原结果）]
	 */
	public static function sign($BDUSS, $tieba, $fid = NULL) {
		if ($fid === NULL) {
			$fid = TiebaForum::getFid($tieba);
		}
		$data = [
			'BDUSS' => $BDUSS,
			'_client_id' => TiebaCommon::getClient('_client_id'),
			'_client_type' => TiebaCommon::getClient('_client_type'),
			'_client_version' => TiebaCommon::getClient('_client_version'),
			'_phone_imei' => TiebaCommon::getClient('_phone_imei'),
			'fid' => $fid,
			'kw' => $tieba,
			'net_type' => TiebaCommon::getClient('net_type'),
			'tbs' => TiebaCommon::getTbs($BDUSS)
		];
		$data['sign'] = TiebaCommon::clientSign($data);
		//请求
		$r = json_decode(TiebaCommon::fetchUrl(TiebaCommon::createUrl('c/c/forum/sign'), ['post' => $post]), 1);
		if (!$r) {
			throw new TiebaException('Network error');
		}
		if ($re['user_info']) {
			return [1, $re['user_info']['sign_bonus_point']];
		} else {
			switch($re['error_code']){
				case '160002': //已经签过
					$reason = 'signed';
					break;
				case '1': //未登录
					$reason = 'wrong_cookie';
					break;
				case '340006': //贴吧出现问题
					$reason = 'tieba_blocked';
					break;
				default:
					$reason = 'unknow';
					break;
			}
			return [0, $reason, $re['error_msg']];
		}
	}
}