<?
use Bitrix\Main\Localization\Loc;

$sTableID = "b_iblock"; // ID таблицы

$oSort = new CAdminSorting($sTableID, "ID"); // объект сортировки
$lAdmin = new CAdminList($sTableID, $oSort); // основной объект списка

// Устанавливаем заголовки таблицы
$arHeaders = array(
    array(
		"id" => "ID", 
		"content" => Loc::GetMessage('TABLE_HEADER_ID'), 
		"sort" => "ID", 
		"default" => true),
    array(
		"id" => "NAME",
		 "content" => Loc::GetMessage('TABLE_HEADER_NAME'),
		 "sort" => "NAME",
		 "default" => true),
    array(
		"id" => "IBLOCK_TYPE_ID", 
		"content" => Loc::GetMessage('TABLE_HEADER_TYPE'), 
		"sort" => "IBLOCK_TYPE_ID", 
		"default" => true),
    // Добавьте другие колонки по необходимости
);

// Добавляем заголовки в таблицу
$lAdmin->AddHeaders($arHeaders);

// Вытаскиваем все инфоблоки
if (CModule::IncludeModule('iblock')){
	$arOrder = Array('ID' => 'ASC');
	$arFilter = Array();
	if ($rsData = CIblock::GetList($arOrder, $arFilter)){
		while($arRes = $rsData->Fetch()):
			// $arRes - это отдельный инфоблок

			// создаем строку. результат - экземпляр класса CAdminListRow
			$row =& $lAdmin->AddRow($arRes['ID'], $arRes);
		  
		// 	// далее настроим отображение значений при просмотре и редактировании списка
		  
		// 	// параметр NAME будет редактироваться как текст, а отображаться ссылкой
		// 	$row->AddInputField("NAME", array("size"=>20));
		// 	$row->AddViewField("NAME", '<a href="rubric_edit.php?ID='.$f_ID.'&lang='.LANG.'">'.$f_NAME.'</a>');
		  
		// 	// параметр LID будет редактироваться в виде выпадающего списка языков
		// 	$row->AddEditField("LID", CLang::SelectBox("LID", $f_LID)); 
		  
		// 	// параметр SORT будет редактироваться текстом
		// 	$row->AddInputField("SORT", array("size"=>20)); 
		  
		// 	// флаги ACTIVE и VISIBLE будут редактироваться чекбоксами
		// 	$row->AddCheckField("ACTIVE"); 
		// 	$row->AddCheckField("VISIBLE");
		  
		// 	// параметр AUTO будет отображаться в виде "Да" или "Нет", полужирным при редактировании
		// 	$row->AddViewField("AUTO", $f_AUTO=="Y"?GetMessage("POST_U_YES"):GetMessage("POST_U_NO")); 
		// 	$row->AddEditField("AUTO", "<b>".($f_AUTO=="Y"?GetMessage("POST_U_YES"):GetMessage("POST_U_NO"))."</b>");
			// сформируем контекстное меню
			$arActions = Array();
		// 	// редактирование элемента
		// 	$arActions[] = array(
		// 		"ICON"=>"edit",
		// 		"DEFAULT"=>true,
		// 		"TEXT"=>GetMessage("rub_edit"),
		// 		"ACTION"=>$lAdmin->ActionRedirect("rubric_edit.php?ID=".$f_ID)
		// 	);
		  
			// выбор элемента
			if ($POST_RIGHT>="W")
				$arActions[] = array(
					"ICON"=>"view",
					"TEXT"=>GetMessage("TABLE_ACTION_RESEARCH"),
					"ACTION"=>$lAdmin->ActionRedirect("propertymanager.php?mode=view&iblockID=".$arRes['ID'])
				);
		// 	// вставим разделитель
		// 	$arActions[] = array("SEPARATOR"=>true);
		// 	// проверка шаблона для автогенерируемых рассылок
		// 	if (strlen($f_TEMPLATE)>0 && $f_AUTO=="Y")
		// 		$arActions[] = array(
		// 			"ICON"=>"",
		// 			"TEXT"=>GetMessage("rub_check"),
		// 			"ACTION"=>$lAdmin->ActionRedirect("template_test.php?ID=".$f_ID)
		// 		);
		// 	// если последний элемент - разделитель, почистим мусор.
		// 	if(is_set($arActions[count($arActions)-1], "SEPARATOR"))
		// 		unset($arActions[count($arActions)-1]);
		  
		// 	// применим контекстное меню к строке
			$row->AddActions($arActions);
		endwhile;
	}
}

// $row->AddActions();

// Debug::dump($lAdmin);
// $by = mb_strtoupper($lAdmin->getField());
// $order = mb_strtoupper($lAdmin->getOrder());
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
  "find_id",
);
// инициализируем фильтр
$lAdmin->InitFilter($FilterArr);

// если все значения фильтра корректны, обработаем его
if (CheckFilter())
{
	// создадим массив фильтрации для выборки CRubric::GetList() на основе значений фильтра
	$arFilter = Array(
		"ID"    => ($find!="" && $find_type == "id"? $find:$find_id),
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
if(($arID = $lAdmin->GroupAction()) && $POST_RIGHT=="W"){
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
	foreach($arID as $ID){
		if(strlen($ID)<=0)
			continue;
		$ID = IntVal($ID);
        
		// для каждого элемента совершим требуемое действие
		switch($action){
			// удаление
			case "view":
				header('Location ', 'https://google.com/');

				// @set_time_limit(0);
				// $DB->StartTransaction();
				// if(!CRubric::Delete($ID))
				// {
				// 	$DB->Rollback();
				// 	$lAdmin->AddGroupError(Loc::getMessage("rub_del_err"), $ID);
				// }
				// $DB->Commit();
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

/*
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
	)
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
*/
// резюме таблицы
$lAdmin->AddFooter(
	array(
		array("title"=>'Loc::getMessage("MAIN_ADMIN_LIST_SELECTED")', "value"=>'$rsData->SelectedRowsCount()'), // кол-во элементов
		array("counter"=>true, "title"=>'Loc::getMessage("MAIN_ADMIN_LIST_CHECKED")', "value"=>"0"), // счетчик выбранных элементов
	)
);
/*
// групповые действия
$lAdmin->AddGroupActionTable(Array(
	"delete"=>Loc::getMessage("MAIN_ADMIN_LIST_DELETE"), // удалить выбранные элементы
	"activate"=>Loc::getMessage("MAIN_ADMIN_LIST_ACTIVATE"), // активировать выбранные элементы
	"deactivate"=>Loc::getMessage("MAIN_ADMIN_LIST_DEACTIVATE"), // деактивировать выбранные элементы
));
// ******************************************************************** //
//                АДМИНИСТРАТИВНОЕ МЕНЮ                                 //
// ******************************************************************** //
  */
// сформируем меню из одного пункта - добавление рассылки
// $aContext = array(
// 	array(
// 		"TEXT"=>'Loc::getMessage("POST_ADD")',
// 		"LINK"=>"rubric_edit.php?lang=".LANG,
// 		"TITLE"=>'Loc::getMessage("POST_ADD_TITLE")',
// 		"ICON"=>"btn_new",
// 	),
// );
// и прикрепим его к списку
// $lAdmin->AddAdminContextMenu($aContext);
/*
// ******************************************************************** //
//                ВЫВОД                                                 //
// ******************************************************************** //
// альтернативный вывод
$lAdmin->CheckListMode();
// установим заголовок страницы
$APPLICATION->SetTitle(Loc::getMessage("rub_title"));
// не забудем разделить подготовку данных и вывод
*/
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

// установим заголовок страницы
$APPLICATION->SetTitle(Loc::getMessage('TITLE_IBLOCKS'));

// Debug::dump(getIblocksByType('1c_catalog'));
// Debug::dump($iBlocks);

// Отобразим таблицу
// $lAdmin->Display();
// ******************************************************************** //
//                ВЫВОД ФИЛЬТРА                                         //
// ******************************************************************** //
// создадим объект фильтра

$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		"ID",
		)
	);
		
?>
<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
<?php
// $oFilter->Begin();
/*
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
echo SelectBoxFromArray("find_visible", $arr, $find_visible, Loc::getMessage("POST_ALL"), "");
?>
</td>
</tr>
<tr>
	<td><?=Loc::getMessage("rub_f_auto")?>:</td>
	<td><?php
echo SelectBoxFromArray("find_auto", $arr, $find_auto, Loc::getMessage("POST_ALL"), "");
?></td>
</tr>
<?php
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form"=>"find_form"));
?>
*/
// $oFilter->End();
?>
</form>
<?
$lAdmin->Display();