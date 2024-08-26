<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;


use Bitrix\Main\Diag\Debug;
// подключим все необходимые файлы:
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог
require_once(GetDirPath(__FILE__).'prolog.php'); // пролог модуля





// выведем таблицу списка элементов

// Поставим роутер по GET запросу
if (!$_GET){
	require_once(GetDirPath(__FILE__).'iblocks_table.php');
} else {
	// Debug::dump($_GET);
	if (isset($_GET['mode'])){
		switch ($_GET['mode']) {
			case 'view':
				// Debug::dump($_GET['iblockID']);
				require_once(GetDirPath(__FILE__).'properties.php');
				break;
			case 'view_elements':
				require_once(GetDirPath(__FILE__).'elements.php');
			default:
				# code...
				break;
		}
	}
}
	
// завершение страницы
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
