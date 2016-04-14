<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Customizing/global/plugins/Services/Cron/CronHook/DclContentImporter/classes/Helper/class.srDclContentImporterMultiLineInputGUI.php');
require_once('./Customizing/global/plugins/Services/Cron/CronHook/DclContentImporter/classes/Config/class.srDclContentImporterConfig.php');

/** Class srDclContentImporterConfigFormGUI
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 */
class srDclContentImporterConfigFormGUI extends ilPropertyFormGUI
{

    /**
     * @var ilDclContentImporterConfigGUI
     */
    protected $parent_gui;
    /**
     * @var  ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilDclContentImporterPlugin
     */
    protected $pl;
    /**
     * @var ilLanguage
     */
    protected $lng;


    /**
     * @param ilDclContentImporterConfigGUI $parent_gui
     */
    public function __construct(ilDclContentImporterConfigGUI $parent_gui)
    {
        global $ilCtrl, $lng;
        $this->parent_gui = $parent_gui;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->pl = ilDclContentImporterPlugin::getInstance();
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $this->setTitle('DataCollection Content Importer');
        $this->initForm();
    }


    /**
     * @param $field
     *
     * @return string
     */
    public function txt($field)
    {
        return $this->pl->txt('admin_form_' . $field);
    }


    protected function initForm()
    {
        global $rbacreview, $ilUser;

        $multiinput = new srDclContentImporterMultiLineInputGUI("DataCollections", srDclContentImporterConfig::F_DCL_CONFIG);
        $multiinput->setInfo("DataCollection-Ref-ID, DataCollection-Table-ID, Import-File, Remove old entries");
        $multiinput->setTemplateDir($this->pl->getDirectory());

        $ref_id_item = new ilTextInputGUI('Datacollection Ref-ID', srDclContentImporterConfig::F_DCL_REF_ID);
        $multiinput->addInput($ref_id_item);

        $table_id_item = new ilTextInputGUI('Datacollection Table-ID', srDclContentImporterConfig::F_DCL_TABLE_ID);
        $multiinput->addInput($table_id_item);

        $import_file = new ilTextInputGUI('Import-File', srDclContentImporterConfig::F_DCL_IMPORT_FILE);
        $multiinput->addInput($import_file);
        
        $remove_old_entries = new ilCheckboxInputGUI('Remove old entries', srDclContentImporterConfig::F_DCL_REMOVE_OLD_ENTRIES);
        $remove_old_entries->setValue(1);
        $multiinput->addInput($remove_old_entries);

        $this->addItem($multiinput);

        $this->addCommandButtons();
    }


    public function fillForm()
    {
        $array = array();
        foreach ($this->getItems() as $item) {
            $this->getValuesForItem($item, $array);
        }
        $this->setValuesByArray($array);
    }


    /**
     * @param ilFormPropertyGUI $item
     * @param                   $array
     *
     * @internal param $key
     */
    private function getValuesForItem($item, &$array)
    {
        if (self::checkItem($item)) {
            $key = $item->getPostVar();
            $array[$key] = srDclContentImporterConfig::getConfigValue($key);
            if (self::checkForSubItem($item)) {
                foreach ($item->getSubItems() as $subitem) {
                    $this->getValuesForItem($subitem, $array);
                }
            }
        }
    }


    /**
     * @return bool
     */
    public function saveObject()
    {
        if (!$this->checkInput()) {
            return false;
        }
        foreach ($this->getItems() as $item) {
            $this->saveValueForItem($item);
        }

        return true;
    }


    /**
     * @param  ilFormPropertyGUI $item
     */
    private function saveValueForItem($item)
    {
        if (self::checkItem($item)) {
            $key = $item->getPostVar();

            srDclContentImporterConfig::set($key, $this->getInput($key));
            if (self::checkForSubItem($item)) {
                foreach ($item->getSubItems() as $subitem) {
                    $this->saveValueForItem($subitem);
                }
            }
        }
    }


    /**
     * @param $item
     *
     * @return bool
     */
    public static function checkForSubItem($item)
    {
        return !$item instanceof ilFormSectionHeaderGUI AND !$item instanceof ilMultiSelectInputGUI and !$item instanceof ilEMailInputGUI;
    }


    /**
     * @param $item
     *
     * @return bool
     */
    public static function checkItem($item)
    {
        return !$item instanceof ilFormSectionHeaderGUI;
    }


    protected function addCommandButtons()
    {
        $this->addCommandButton('save', $this->lng->txt('save'));
        $this->addCommandButton('cancel', $this->lng->txt('cancel'));
    }
}