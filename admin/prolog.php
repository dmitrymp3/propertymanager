<?
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Diag\Debug;

error_reporting(E_ALL);
ini_set('display_errors', 1);

$MODULE_NAME = 'propertymanager';

Loader::includeModule($MODULE_NAME); // инициализация модуля
Loc::loadMessages(GetDirPath(__FILE__).'index.php'); // подгрузка языкового файла

// получим права доступа текущего пользователя на модуль
$POST_RIGHT = $APPLICATION->GetGroupRight($MODULE_NAME);
// если нет прав - отправим к форме авторизации с сообщением об ошибке
if ($POST_RIGHT == "D")
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));

// Вспомогательные функции

// Получить все типы инфоблоков
function getIblockTypes (){
	$iBlocksTypes = [];
	if (CModule::IncludeModule('iblock')){
		if ($res = CIblockType::GetList(Array(), Array())){
			while ($ob = $res->Fetch()){
				$iBlocksTypes[] = $ob;
			}
		}
	}
	return $iBlocksTypes;
}

// Получить все инфоблоки определенного типа
function getIblocksByType ($iblockTypeID){
	if (CModule::IncludeModule('iblock')){
		$iBlocks = [];
		$arOrder = Array('ID' => 'ASC');
		$arFilter = Array(
			'TYPE' => $iblockTypeID
		);
		if ($res = CIblock::GetList($arOrder, $arFilter)){
			while ($iblock = $res->Fetch()){
				$iBlocks[$iblock['ID']] = $iblock;
			}
		}
	}
	return $iBlocks;
}

// Получить все свойства определенного инфоблока
function loadPropertiesList($iblockID) {

    // Получаем список всех свойств инфоблока
    $propertiesList = Array();
    $res = CIBlock::GetProperties($iblockID);
    while ($prop = $res->Fetch()){
        // Запишем в формате ИД - КОД СВОЙСТВА
        $propertiesList[$prop['ID']] = [
            'ID' => $prop['ID'],
            'CODE' => $prop['CODE'],
            'NAME' => $prop['NAME']
        ];
    }
    return $propertiesList;
}

// Получить количество элементов в которых свойство заполнено
function getCountOfProperty($propertyCode, $iblockID, $activity){
    if(CModule::IncludeModule('iblock')){
        $arOrder = Array('ID' => 'ASC');
        $arFilter = Array(
            'ACTIVE' => $activity,
            'IBLOCK_ID' => $iblockID,
            '!PROPERTY_' . $propertyCode => false
            // '!PROPERTY_' . 'VSTROENNYY_PAROGENERATOR' => false
        );
        $arSelect = Array('ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_' . $propertyCode, 'ACTIVE');
        $res = CIBlockElement::GetList($arOrder, $arFilter, false, false, $arSelect);
        // $counter = 0;
        // while($ob = $res->Fetch()){
        //     $counter += 1;
        //     // Debug::dump($ob);
        // }
        return $res->SelectedRowsCount();
        // return $counter;
    }

}

