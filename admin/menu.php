<?
// Bitrix\Main\Diag\Debug::dumpToFile('Запущен файл меню модуля propertymanager');
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(GetDirPath(__FILE__).'index.php');

if($APPLICATION->GetGroupRight("form")>"D") // проверка уровня доступа к модулю веб-форм
{
	// сформируем верхний пункт меню
	$aMenu = array(
		"parent_menu" => "global_menu_services", // поместим в раздел "Сервис"
		"sort"        => 100,                    // вес пункта меню
		"url"         => "propertymanager.php",  // ссылка на пункте меню
		"text"        => Loc::GetMessage('MENU_TITLE'),       // текст пункта меню
		"title"       => 'hinting text', // текст всплывающей подсказки
		"icon"        => "util_menu_icon", // малая иконка
		// "page_icon"   => "form_page_icon", // большая иконка
		// "items_id"    => "menu_webforms",  // идентификатор ветви
		// "items"       => array(),          // остальные уровни меню сформируем ниже.
	);
	// далее выберем список веб-форм и добавим для каждой соответствующий пункт меню
	// require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/form/include.php");
	// $z = CForm::GetMenuList(array("LID"=>LANGUAGE_ID));
	// while ($zr=$z->GetNext())
	// {
	// 	if (strlen($zr["MENU"]) > 0)
	// 	{
	// 		// массив каждого пункта формируется аналогично
	// 		$aMenu["items"][] =  array(
	// 			"text" => $zr["MENU"],
	// 			"url"  => "form_result_list.php?lang=".LANGUAGE_ID."&WEB_FORM_ID=".$zr["ID"],
	// 			"icon" => "form_menu_icon",
	// 			"page_icon" => "form_page_icon",
	// 			"more_url"  => array(
	// 				"form_view.php?WEB_FORM_ID=".$zr["ID"],
	// 				"form_result_list.php?WEB_FORM_ID=".$zr["ID"],
	// 				"form_result_edit.php?WEB_FORM_ID=".$zr["ID"],
	// 				"form_result_print.php?WEB_FORM_ID=".$zr["ID"],
	// 				"form_result_view.php?WEB_FORM_ID=".$zr["ID"]
	// 			),
	// 			"title" => GetMessage("FORM_RESULTS_ALT")
	// 		);
	// 	}
	// }
	// если нам нужно добавить ещё пункты - точно так же добавляем элементы в массив $aMenu["items"]
	// ............
	// вернем полученный список
	return $aMenu;
}
// если нет доступа, вернем false
return false;
?>