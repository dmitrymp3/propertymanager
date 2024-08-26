<?

use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Diag\Debug;

Class propertymanager extends CModule {
    var $MODULE_ID = "propertymanager";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;

    function __construct()
    {
        $arModuleVersion = array();
        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path."/version.php");
        if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
            {
            $this->MODULE_VERSION = $arModuleVersion["VERSION"];
            $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
            }
        $this->MODULE_NAME = Loc::getMessage('PROPERTYMANAGER_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('PROPERTYMANAGER_MODULE_DESCRIPTION');
    }

    function InstallFiles()
    {
    CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/propertymanager/install/propertymanager.php",
                $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/propertymanager.php");
        return true;
    }

    function UnInstallFiles()
    {
        \Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"].'/bitrix/admin/propertymanager.php');
        // DeleteDirFilesEx("/bitrix/admin/propertymanager.php");
        return true;
    }

    public function DoInstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;

        Debug::dumpToFile('DoInstall!!!');
        Debug::dumpToFile($this->InstallFiles());
        Debug::dumpToFile($result);
        RegisterModule($this->MODULE_ID);
        $APPLICATION->IncludeAdminFile('Модуль "Менеджер свойств" установлен', $DOCUMENT_ROOT."/local/modules/propmanager/install/step.php");
        
        return true;
    }
    function DoUninstall()
    {
        global $DOCUMENT_ROOT, $APPLICATION;
        $this->UnInstallFiles();
        UnRegisterModule($this->MODULE_ID);
        $APPLICATION->IncludeAdminFile('Модуль "Менеджер свойств" удален', $DOCUMENT_ROOT."/local/modules/dv_module/install/unstep.php");
    }


}
?>