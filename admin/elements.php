<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Diag\Debug;

// Подготовим данные

// Таблица со свойствами
$sTableID = "tbl_elements"; // ID таблицы

$oSort = new CAdminSorting($sTableID, "ID"); // объект сортировки
$lAdmin = new CAdminList($sTableID, $oSort); // основной объект списка


// Устанавливаем заголовки таблицы
$arHeaders = array(
    array(
		"id" => "ACTIVE", 
		"content" => Loc::GetMessage('ELEMENTS_HEADER_ACTIVE'), 
		"sort" => "ACTIVE", 
		"default" => true
    ),
    array(
		"id" => "ID", 
		"content" => Loc::GetMessage('ELEMENTS_HEADER_ID'), 
		"sort" => "ID", 
		"default" => true
    ),
    array(
		"id" => "NAME",
		 "content" => Loc::GetMessage('ELEMENTS_HEADER_NAME'),
		 "sort" => "NAME",
		 "default" => true
        ),
    array(
		"id" => "PROPERTY_VALUE", 
		"content" => Loc::GetMessage('ELEMENTS_HEADER_VALUE'), 
		"sort" => "PROPERTY_VALUE", 
		"default" => true
    ),

    // Добавьте другие колонки по необходимости
);

// Добавляем заголовки в таблицу
$lAdmin->AddHeaders($arHeaders);

// Добавим фильтр

// Массив фильтруемых полей
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



// Заполняем таблицу
if(CModule::IncludeModule('iblock')){
    $arOrder = Array('ID' => 'ASC');
    $arFilter = Array(
        'IBLOCK_ID' => $_GET['iblockID'],
        '!PROPERTY_' . $_GET['property'] => false
    );
    $arSelect = Array('ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_' . $_GET['property'], 'IBLOCK_TYPE_ID', 'ACTIVE');
    // $arSelect = Array();
    $res = CIBlockElement::GetList($arOrder, $arFilter, false, false, $arSelect);
    $res->NavStart(20);
    $lAdmin->NavText($res->GetNavPrint(Loc::GetMessage('NAV_TEXT')));
    while($ob = $res->GetNext()){
        // Debug::dump($ob);
        $ob['PROPERTY_VALUE'] = $ob['PROPERTY_' . $_GET['property'] . '_VALUE'];
        $row = $lAdmin->AddRow($ob['ID'], $ob);
        $row->AddViewField("NAME", '<a href="/bitrix/admin/iblock_element_edit.php?IBLOCK_ID='.$_GET['iblockID'].'&type='.$ob['IBLOCK_TYPE_ID'].'&lang=ru&ID='.$ob['ID'].'">'.$ob['NAME'].'</a>');

        // Добавим действия
        $arActions = Array();
        // выбор элемента
        if ($POST_RIGHT>="W")
            $arActions[] = array(
                "ICON"=>"delete",
                "TEXT"=>GetMessage("ELEMENT_PROPERTY_ACTION_DELETE"),
                "ACTION"=>''
            );
            
	    $row->AddActions($arActions);
    }
    $lAdmin->AddFooter(
        array(
            array("title"=>'Loc::getMessage("MAIN_ADMIN_LIST_SELECTED")', "value"=>'10'), // кол-во элементов
            array("counter"=>true, "title"=>'Loc::getMessage("MAIN_ADMIN_LIST_CHECKED")', "value"=>"0"), // счетчик выбранных элементов
        )
    );
}




$lAdmin->AddGroupActionTable(Array(
	"delete" => Loc::getMessage("MAIN_ADMIN_LIST_DELETE"), // удалить выбранные элементы
	"activate" => Loc::getMessage("MAIN_ADMIN_LIST_ACTIVATE"), // активировать выбранные элементы
	"deactivate" => Loc::getMessage("MAIN_ADMIN_LIST_DEACTIVATE"), // деактивировать выбранные элементы
));



// Разделитель подготовки данных и отображения
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

// установим заголовок страницы
$APPLICATION->SetTitle(Loc::getMessage('TITLE_ELEMENTS') . ' ' . $_GET['property']);



// Debug::dump($_GET);

// Вывод фильтра
// ******************************************************************** //
//                ВЫВОД ФИЛЬТРА                                         //
// ******************************************************************** //
// создадим объект фильтра
/*
$oFilter = new CAdminFilter($sTableID."_filter",["ID"]);
?>
<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">
<?php
$oFilter->Begin();?>
<tr>
	<td><b><?='Поиск по ИД?'?></b></td>
	<td>
		<input type="text" size="25" name="find" value="<?='lkj'?>" title="lll">
		<?php
		
    ?>
	</td>
</tr>


<?php
$oFilter->Buttons(array("table_id"=>$sTableID,"url"=>$APPLICATION->GetCurPage(),"form"=>"find_form"));
$oFilter->End();?>
</form>

<?php
*/    
// Отобразим таблицу
$lAdmin->Display();

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';