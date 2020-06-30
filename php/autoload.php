<?php

/**
 * Autoload
 * 
 * @author ShuangYa
 * @package TiebaSDK
 * @link http://www.sylingd.com/
 * @copyright Copyright (c) 2015 ShuangYa
 * @license http://lab.sylingd.com/go.php?name=framework&type=license
 */

define('TB_ROOT', __DIR__);
spl_autoload_register(function($className) {
	if (in_array($className, ['TiebaCommon', 'TiebaForum', 'TiebaPersonal', 'TiebaPost', 'TiebaManage', 'TiebaException'], TRUE)) {
		require(TB_ROOT . '/' . $className . '.php');
	}
}, TRUE, TRUE);