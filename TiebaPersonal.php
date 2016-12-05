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
	 * 获取基本信息
	 * @access public
	 * @param string $BDUSS
	 */
	public static function getMyInfo($BDUSS) {
		$data = [
			'_client_id' => TiebaCommon::getClient('_client_id'),
			'_client_type' => TiebaCommon::getClient('_client_type'),
			'_client_version' => TiebaCommon::getClient('_client_version'),
			'_phone_imei' => TiebaCommon::getClient('_phone_imei'),
			'bdusstoken' => $BDUSS,
			'from' => 'tieba',
			'net_type' => TiebaCommon::getClient('net_type')
		];
		$data['sign'] = TiebaCommon::clientSign($data);
		$url = TiebaCommon::createUrl('c/s/login');
		$info = json_decode(TiebaCommon::fetchUrl($url, ['post' => $data]), 1);
		//通过$info['anti']['tbs']可以得到tbs
		return $info['user'];
	}
	/**
	 * 获取详细信息
	 * @access public
	 * @param string $BDUSS
	 */
	public static function getMyDetail($BDUSS, $uid = NULL) {
		if (NULL === $uid) {
			$u = self::getMyInfo($BDUSS);
			$uid = $u['id'];
		}
		$data = [
			'BDUSS' => $BDUSS,
			'_client_id' => TiebaCommon::getClient('_client_id'),
			'_client_type' => TiebaCommon::getClient('_client_type'),
			'_client_version' => TiebaCommon::getClient('_client_version'),
			'_phone_imei' => TiebaCommon::getClient('_phone_imei'),
			'from' => 'tieba',
			'need_post_count' => 1,
			'net_type' => TiebaCommon::getClient('net_type'),
			'uid' => $uid
		];
		$data['sign'] = TiebaCommon::clientSign($data);
		$url = TiebaCommon::createUrl('c/u/user/profile');
		$info = json_decode(TiebaCommon::fetchUrl($url, ['post' => $data]), 1);
		return $info;
	}
	/**
	 * 获取我喜欢的贴吧，最高支持200个
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
		$data['sign'] = TiebaCommon::clientSign($data);
		$url = TiebaCommon::createUrl('c/f/forum/like');
		$info = json_decode(TiebaCommon::fetchUrl($url, ['post' => $data]), 1);
		if (!$info) {
			throw new TiebaException('Network error');
		}
		return (array)$info['forum_list'];
	}
	/**
	 * 获取我喜欢的贴吧，速度较慢，但无上限
	 * @access public
	 * @param string $BDUSS
	 * @return array
	 */
	public static function getMyLikeSlow($BDUSS, $STOKEN) {
		$pn = 0;
		$tblist = [];
		do {
			$pn++;
			$url = TiebaCommon::createUrl('f/like/mylike?pn=' . $pn, '');
			$result = TiebaCommon::fetchUrl($url, ['UA' => 'browser', 'cookie' => 'BDUSS=' . $BDUSS . '; STOKEN=' . $STOKEN]);
			$pre_reg = '/<tr>(.*?)<\/tr>/is';
			$result = iconv('GBK', 'UTF-8', $result);
			preg_match_all($pre_reg, $result, $matches);
			//匹配结果会包含th
			$onepagenum = count($matches[0]);
			for ($i = 1; $i < $onepagenum; $i++) {
				//解析
				preg_match('/balvid="(\d+)" balvname="(.*?)"/is', $matches[0][$i], $r);
				$id = $r[1];
				$name = iconv('GBK', 'UTF-8', urldecode($r[2]));
				preg_match('/class="like_badge_title">(.*?)</is', $matches[0][$i], $r);
				$level_name = $r[1];
				preg_match('/class="like_badge_lv">(\d+)</is', $matches[0][$i], $r);
				$level_id = $r[1];
				preg_match('/class="cur_exp"(.*?)>(\d+)</is', $matches[0][$i], $r);
				$cur_score = $r[2];
				$tblist[] = [
					'id' => $id,
					'name' => $name,
					'level_id' => $level_id,
					'level_name' => $level_name,
					'cur_score' => $cur_score
				];
			}
		} while ($onepagenum >= 2);
		return $tblist;
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
		$re = json_decode(TiebaCommon::fetchUrl(TiebaCommon::createUrl('c/c/forum/sign'), ['post' => $data]), 1);
		if (!$re) {
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
			return [0, $reason, isset($re['error_msg']) ? $re['error_msg'] : $re['error']['usermsg']];
		}
	}
}