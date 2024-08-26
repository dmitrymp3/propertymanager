<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
// подключим все необходимые файлы:
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php"); // первый общий пролог
Loader::includeModule('propertymanager'); // инициализация модуля
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/prolog.php"); // пролог модуля
// явного подключения языкового файла не требуется
// получим права доступа текущего пользователя на модуль
$POST_RIGHT = $APPLICATION->GetGroupRight("propertymanager");
// если нет прав - отправим к форме авторизации с сообщением об ошибке
if ($POST_RIGHT == "D")
$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
// здесь будет вся серверная обработка и подготовка данных

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php"); // второй общий пролог
// здесь будет вывод страницы

$sTableID = "tbl_rubric"; // ID таблицы
$oSort = new CAdminSorting($sTableID, "ID", "desc"); // объект сортировки
$lAdmin = new CAdminList($sTableID, $oSort); // основной объект списка
$by = mb_strtoupper($lAdmin->getField());
$order = mb_strtoupper($lAdmin->getOrder());
// ******************************************************************** //
//                           ФИЛЬТР                                     //
// ******************************************************************** //
// *********************** CheckFilter ******************************** //
// проверку значений фильтра для удобства вынесем в отдельную функцию
function CheckFilter()
{
	global $FilterArr, $lAdmin;
	foreach ($FilterArr as $f) global $$f;
	// В данном случае проверять нечего. 
	// В общем случае нужно проверять значения переменных $find_имя
	// и в случае возниконовения ошибки передавать ее обработчику 
	// посредством $lAdmin->AddFilterError('текст_ошибки').
  
	return count($lAdmin->arFilterErrors)==0; // если ошибки есть, вернем false;
}
// *********************** /CheckFilter ******************************* //
// опишем элементы фильтра
$FilterArr = Array(
  "find",
  "find_type",
  "find_id",
  "find_lid",
  "find_active",
  "find_visible",
  "find_auto",
);
// инициализируем фильтр
$lAdmin->InitFilter($FilterArr);
// если все значения фильтра корректны, обработаем его
if (CheckFilter())
{
	// создадим массив фильтрации для выборки CRubric::GetList() на основе значений фильтра
	$arFilter = Array(
		"ID"    => ($find!="" && $find_type == "id"? $find:$find_id),
		"LID"    => $find_lid,
		"ACTIVE"  => $find_active,
		"VISIBLE"  => $find_visible,
		"AUTO"    => $find_auto,
	);
}
// ******************************************************************** //
//                ОБРАБОТКА ДЕЙСТВИЙ НАД ЭЛЕМЕНТАМИ СПИСКА              //
// ******************************************************************** //
// сохранение отредактированных элементов
if($lAdmin->EditAction() && $POST_RIGHT=="W")
{
	// пройдем по списку переданных элементов
	foreach($lAdmin->GetEditFields() as $ID=>$arFields)
	{ 
    
		// сохраним изменения каждого элемента
		$DB->StartTransaction();
		$ID = IntVal($ID);
		$cData = new CRubric;
		if(($rsData = $cData->GetByID($ID)) && ($arData = $rsData->Fetch()))
		{
			foreach($arFields as $key=>$value)
				$arData[$key]=$value;
			if(!$cData->Update($ID, $arData))
			{
				$lAdmin->AddGroupError(Loc::getMessage("rub_save_error")." ".$cData->LAST_ERROR, $ID);
				$DB->Rollback();
			}
		}
		else
		{
			$lAdmin->AddGroupError(Loc::getMessage("rub_save_error")." ".Loc::getMessage("rub_no_rubric"), $ID);
			$DB->Rollback();
		}
		$DB->Commit();
	}
}
// обработка одиночных и групповых действий
if(($arID = $lAdmin->GroupAction()) && $POST_RIGHT=="W")
{
// если выбрано "Для всех элементов"
if ($lAdmin->IsGroupActionToAll())
	{
		$cData = new CRubric;
		$rsData = $cData->GetList(array($by=>$order), $arFilter);
		while($arRes = $rsData->Fetch())
			$arID[] = $arRes['ID'];
	}
$action = $lAdmin->GetAction();
 // пройдем по списку элементов
	foreach($arID as $ID)
	{
		if(strlen($ID)<=0)
			continue;
		$ID = IntVal($ID);
        
		// для каждого элемента совершим требуемое действие
		switch($action)
		{
		// удаление
		case "delete":
			@set_time_limit(0);
			$DB->StartTransaction();
			if(!CRubric::Delete($ID))
			{
				$DB->Rollback();
				$lAdmin->AddGroupError(Loc::getMessage("rub_del_err"), $ID);
			}
			$DB->Commit();
			break;
    
		// активация/деактивация
		case "activate":
		case "deactivate":
			$cData = new CRubric;
			if(($rsData = $cData->GetByID($ID)) && ($arFields = $rsData->Fetch()))
			{
				$arFields["ACTIVE"]=($_REQUEST['action']=="activate"?"Y":"N");
				if(!$cData->Update($ID, $arFields))
					$lAdmin->AddGroupError(Loc::getMessage("rub_save_error").$cData->LAST_ERROR, $ID);
			}
			else
				$lAdmin->AddGroupError(Loc::getMessage("rub_save_error")." ".Loc::getMessage("rub_no_rubric"), $ID);
			break;
		}
	}
}
// ******************************************************************** //
//                ВЫБОРКА ЭЛЕМЕНТОВ СПИСКА                              //
// ******************************************************************** //
// выберем список рассылок
$cData = new CRubric;
$rsData = $cData->GetList(array($by=>$order), $arFilter);
// преобразуем список в экземпляр класса CAdminResult
$rsData = new CAdminResult($rsData, $sTableID);
// аналогично CDBResult инициализируем постраничную навигацию.
$rsData->NavStart();
// отправим вывод переключателя страниц в основной объект $lAdmin
$lAdmin->NavText($rsData->GetNavPrint(Loc::getMessage("rub_nav")));
// ******************************************************************** //
//                ПОДГОТОВКА СПИСКА К ВЫВОДУ                            //
// ******************************************************************** //
/*
$lAdmin->AddHeaders(array(
	array(  "id"    =>"ID",
		"content"  =>"ID",
		"sort"    =>"id",
		"align"    =>"right",
		"default"  =>true,
	),
	array(  "id"    =>"NAME",
		"content"  =>Loc::getMessage("rub_name"),
		"sort"    =>"name",
		"default"  =>true,
	),
	array(  "id"    =>"LID",
		"content"  =>Loc::getMessage("rub_site"),
		"sort"    =>"lid",
		"default"  =>true,
	),
	array(  "id"    =>"SORT",
		"content"  =>Loc::getMessage("rub_sort"),
		"sort"    =>"sort",
		"align"    =>"right",
		"default"  =>true,
	),
	array(  "id"    =>"ACTIVE",
		"content"  =>Loc::getMessage("rub_act"),
		"sort"    =>"act",
		"default"  =>true,
	),
	array(  "id"    =>"VISIBLE",
		"content"  =>Loc::getMessage("rub_visible"),
		"sort"    =>"visible",
		"default"  =>true,
	),
	array(  "id"    =>"AUTO",
		"content"  =>Loc::getMessage("rub_auto"),
		"sort"    =>"auto",
		"default"  =>true,
	),
	array(  "id"    =>"LAST_EXECUTED",
		"content"  =>Loc::getMessage("rub_last_exec"),
		"sort"    =>"last_executed",
		"default"  =>true,
	),
));
while($arRes = $rsData->NavNext(true, "f_")):
  
	// создаем строку. результат - экземпляр класса CAdminListRow
	$row =& $lAdmin->AddRow($f_ID, $arRes); 
  
	// далее настроим отображение значений при просмотре и редаткировании списка
  
	// параметр NAME будет редактироваться как текст, а отображаться ссылкой
	$row->AddInputField("NAME", array("size"=>20));
	$row->AddViewField("NAME", '<a href="rubric_edit.php?ID='.$f_ID.'&lang='.LANG.'">'.$f_NAME.'</a>');
  
	// параметр LID будет редактироваться в виде выпадающего списка языков
	$row->AddEditField("LID", CLang::SelectBox("LID", $f_LID)); 
  
	// параметр SORT будет редактироваться текстом
	$row->AddInputField("SORT", array("size"=>20)); 
  
	// флаги ACTIVE и VISIBLE будут редактироваться чекбоксами
	$row->AddCheckField("ACTIVE"); 
	$row->AddCheckField("VISIBLE");
  
	// параметр AUTO будет отображаться в виде "Да" или "Нет", полужирным при редактировании
	$row->AddViewField("AUTO", $f_AUTO=="Y"?Loc::getMessage("POST_U_YES"):Loc::getMessage("POST_U_NO")); 
	$row->AddEditField("AUTO", "<b>".($f_AUTO=="Y"?Loc::getMessage("POST_U_YES"):Loc::getMessage("POST_U_NO"))."</b>");
	// сформируем контекстное меню
	$arActions = Array();
	// редактирование элемента
	$arActions[] = array(
		"ICON"=>"edit",
		"DEFAULT"=>true,
		"TEXT"=>Loc::getMessage("rub_edit"),
		"ACTION"=>$lAdmin->ActionRedirect("rubric_edit.php?ID=".$f_ID)
	);
  
	// удаление элемента
	if ($POST_RIGHT>="W")
		$arActions[] = array(
			"ICON"=>"delete",
			"TEXT"=>Loc::getMessage("rub_del"),
			"ACTION"=>"if(confirm('".Loc::getMessage('rub_del_conf')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete")
		);
	// вставим разделитель
	$arActions[] = array("SEPARATOR"=>true);
	// проверка шаблона для автогенерируемых рассылок
	if (strlen($f_TEMPLATE)>0 && $f_AUTO=="Y")
		$arActions[] = array(
			"ICON"=>"",
			"TEXT"=>Loc::getMessage("rub_check"),
			"ACTION"=>$lAdmin->ActionRedirect("template_test.php?ID=".$f_ID)
		);
	// если последний элемент - разделитель, почистим мусор.
	if(is_set($arActions[count($arActions)-1], "SEPARATOR"))
		unset($arActions[count($arActions)-1]);
  
	// применим контекстное меню к строке
	$row->AddActions($arActions);
endwhile;
// резюме таблицы
$lAdmin->AddFooter(
	array(
		array("title"=>Loc::getMessage("MAIN_ADMIN_LIST_SELECTED"), "value"=>$rsData->SelectedRowsCount()), // кол-во элементов
		array("counter"=>true, "title"=>Loc::getMessage("MAIN_ADMIN_LIST_CHECKED"), "value"=>"0"), // счетчик выбранных элементов
	)
);
// групповые действия
$lAdmin->AddGroupActionTable(Array(
	"delete"=>Loc::getMessage("MAIN_ADMIN_LIST_DELETE"), // удалить выбранные элементы
	"activate"=>Loc::getMessage("MAIN_ADMIN_LIST_ACTIVATE"), // активировать выбранные элементы
	"deactivate"=>Loc::getMessage("MAIN_ADMIN_LIST_DEACTIVATE"), // деактивировать выбранные элементы
));
// ******************************************************************** //
//                АДМИНИСТРАТИВНОЕ МЕНЮ                                 //
// ******************************************************************** //
  
// сформируем меню из одного пункта - добавление рассылки
$aContext = array(
	array(
		"TEXT"=>Loc::getMessage("POST_ADD"),
		"LINK"=>"rubric_edit.php?lang=".LANG,
		"TITLE"=>Loc::getMessage("POST_ADD_TITLE"),
		"ICON"=>"btn_new",
	),
);
// и прикрепим его к списку
$lAdmin->AddAdminContextMenu($aContext);
// ******************************************************************** //
//                ВЫВОД                                                 //
// ******************************************************************** //
// альтернативный вывод
$lAdmin->CheckListMode();
// установим заголовок страницы
$APPLICATION->SetTitle(Loc::getMessage("rub_title"));
// не забудем разделить подготовку данных и вывод
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
// ******************************************************************** //
//                ВЫВОД ФИЛЬТРА                                         //
// ******************************************************************** //
// создадим объект фильтра
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		"ID",
		Loc::getMessage("rub_f_site"),
		Loc::getMessage("rub_f_active"),
		Loc::getMessage("rub_f_public"),
		Loc::getMessage("rub_f_auto"),
	)
);
?>
<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
<?php
$oFilter->Begin();
?>
<tr>
	<td><b><?=Loc::getMessage("rub_f_find")?>:</b></td>
	<td>
		<input type="text" size="25" name="find" value="<?echo htmlspecialchars($find)?>" title="<?=Loc::getMessage("rub_f_find_title")?>">
		<?php
		$arr = array(
			"reference" => array(
			"ID",
		),
		"reference_id" => array(
			"id",
		)
	);
	echo SelectBoxFromArray("find_type", $arr, $find_type, "", "");
	?>
	</td>
</tr>
<tr>
	<td><?="ID"?>:</td>
	<td>
		<input type="text" name="find_id" size="47" value="<?echo htmlspecialchars($find_id)?>">
	</td>
</tr>
<tr>
	<td><?=Loc::getMessage("rub_f_site").":"?></td>
	<td><input type="text" name="find_lid" size="47" value="<?echo htmlspecialchars($find_lid)?>"></td>
</tr>
<tr>
	<td><?=Loc::getMessage("rub_f_active")?>:</td>
	<td>
		<?php
		$arr = array(
			"reference" => array(
			Loc::getMessage("POST_YES"),
			Loc::getMessage("POST_NO"),
		),
		"reference_id" => array(
			"Y",
			"N",
		)
	);
	echo SelectBoxFromArray("find_active", $arr, $find_active, Loc::getMessage("POST_ALL"), "");
	?>
	</td>
</tr>
<tr>
	<td><?=Loc::getMessage("rub_f_public")?>:</td>
	<td><?php
echo SelectBoxFromArray("find_visible", $arr, $find_visible, Loc::getMessage("POST_ALL"), "");</td>
?>
</tr>
<tr>
	<td><?=Loc::getMessage("rub_f_auto")?>:</td>
	<td><?php
echo SelectBoxFromArray("find_auto", $arr, $find_auto, Loc::getMessage("POST_ALL"), "");
?>
</td>
</tr>
<?php
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form"=>"find_form"));
$oFilter->End();
?>
</form>
<?php
// выведем таблицу списка элементов
$lAdmin->DisplayList();
*/

// завершение страницы
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");


require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");