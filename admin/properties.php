<?
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Diag\Debug;

// Обработаем GET-запрос на удаление
// Если установлено свойство к удалению
if (isset($_GET['property_to_delete'])){

  if(CIBlockProperty::Delete($_GET['property_to_delete'])){
    // Выведем уведомление об успешном удалении
    $message = new CAdminMessage([
      'MESSAGE'=> Loc::GetMessage('PROPERTY_DELETE_SUCCESSFUL') . ' ' . $_GET['property_to_delete'], 
      'TYPE'=>'OK', 
      // 'DETAILS'=>'this is detailed message', 
      'HTML'=>False
      ]);
      
    } else {
      $message = new CAdminMessage([
        'MESSAGE'=> Loc::GetMessage('PROPERTY_DELETE_FAIL') . ' ' . $_GET['property_to_delete'], 
        'TYPE'=>'ERROR', 
        // 'DETAILS'=>'this is detailed message', 
        'HTML'=>False
        ]);
    }
    
  
}




// Подготовим данные

// Таблица со свойствами
$sTableID = "tbl_iblocks_properties"; // ID таблицы

$oSort = new CAdminSorting($sTableID, "ID"); // объект сортировки
$lAdmin = new CAdminList($sTableID, $oSort); // основной объект списка

// Устанавливаем заголовки таблицы
$arHeaders = array(
    array(
		"id" => "ID", 
		"content" => Loc::GetMessage('PROPERTY_HEADER_ID'), 
		"sort" => "ID", 
		"default" => true),
    array(
		"id" => "NAME",
		 "content" => Loc::GetMessage('PROPERTY_HEADER_NAME'),
		 "sort" => "NAME",
		 "default" => true),
    array(
		"id" => "CODE", 
		"content" => Loc::GetMessage('PROPERTY_HEADER_CODE'), 
		"sort" => "CODE", 
		"default" => true),
    array(
		"id" => "COUNTER_ACTIVE", 
		"content" => Loc::GetMessage('PROPERTY_HEADER_COUNTER_ACTIVE'), 
		"sort" => "COUNTER_ACTIVE", 
		"default" => true),
    array(
		"id" => "COUNTER_UNACTIVE", 
		"content" => Loc::GetMessage('PROPERTY_HEADER_COUNTER_UNACTIVE'), 
		"sort" => "COUNTER_UNACTIVE", 
		"default" => true),
    // Добавьте другие колонки по необходимости
);

// Добавляем заголовки в таблицу
$lAdmin->AddHeaders($arHeaders);

$propertiesList = loadPropertiesList($_GET['iblockID']);

foreach ($propertiesList as $id => $property) {
    // Получим количество
    $property['COUNTER_ACTIVE'] = getCountOfProperty($property['CODE'], $_GET['iblockID'], $activity = 'Y');
    $property['COUNTER_UNACTIVE'] = getCountOfProperty($property['CODE'], $_GET['iblockID'], $activity = 'N');

    // Добавим строчку
    $row = $lAdmin->AddRow($id, $property);

    // Добавим действия
    $arActions = Array();
    // выбор элемента
    if ($POST_RIGHT>="W")
    $arActions[] = array(
        "ICON"=>"view",
        "TEXT"=>GetMessage("PROPERTY_ACTION_RESEARCH"),
        "ACTION"=>$lAdmin->ActionRedirect("propertymanager.php?mode=view_elements&iblockID=".$_GET['iblockID']."&property=".$property['CODE'])
        );
    $arActions[] = array(
        "ICON"=>"delete",
        "TEXT"=>GetMessage("PROPERTY_ACTION_DELETE"),
        "ACTION"=>$lAdmin->ActionRedirect("propertymanager.php?mode=view&iblockID=".$_GET['iblockID']."&property_to_delete=".$id)
        // "ACTION"=>"if(confirm('".Loc::getMessage('PROPERTY_CONFIRM_DELETE')."')) ".$lAdmin->ActionDoGroup($id, "delete", 'param1=bugaga')
    );

	$row->AddActions($arActions);
}



// Разделитель подготовки данных и отображения
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

// установим заголовок страницы
$APPLICATION->SetTitle(Loc::getMessage('TITLE_PROPERTIES'));

// Если есть что отобразить в уведомлении - отображаем
if (isset($message))
  echo $message->Show();

// Отобразим таблицу
$lAdmin->Display();





// Debug::dump($lAdmin->GetAction());



    
