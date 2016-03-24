<?php
require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
require_once('./Customizing/global/plugins/Services/Cron/CronHook/PHBernArbeitenarchivImporter/classes/Helper/class.srPHBernArbeitenarchivImporterMultiLineInputGUI.php');
require_once('./Customizing/global/plugins/Services/Cron/CronHook/PHBernArbeitenarchivImporter/classes/Config/class.srPHBernArbeitenarchivImporterConfig.php');

/**
 * Class srPHBernArbeitenarchivImporterConfigFormGUI
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 */
class srPHBernArbeitenarchivImporterConfigFormGUI extends ilPropertyFormGUI
{

    /**
     * @var srPHBernArbeitenarchivConfigGUI
     */
    protected $parent_gui;
    /**
     * @var  ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilPHBernArbeitenarchivImporterPlugin
     */
    protected $pl;
    /**
     * @var ilLanguage
     */
    protected $lng;


    /**
     * @param ilPHBernArbeitenarchivImporterConfigGUI $parent_gui
     */
    public function __construct(ilPHBernArbeitenarchivImporterConfigGUI $parent_gui)
    {
        global $ilCtrl, $lng;
        $this->parent_gui = $parent_gui;
        $this->ctrl = $ilCtrl;
        $this->lng = $lng;
        $this->pl = ilPHBernArbeitenarchivImporterPlugin::getInstance();
        $this->setFormAction($this->ctrl->getFormAction($this->parent_gui));
        $this->setTitle('PHBern Arbeitenarchiv');
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

        $multiinput = new srPHBernArbeitenarchivImporterMultiLineInputGUI("DataCollections", srPHBernArbeitenarchivImporterConfig::F_DCL_CONFIG);
        $multiinput->setInfo("DataCollection-Ref-ID, DataCollection-Table-ID, Import-File, Remove old entries");
        $multiinput->setTemplateDir($this->pl->getDirectory());

        $ref_id_item = new ilTextInputGUI('Datacollection Ref-ID', srPHBernArbeitenarchivImporterConfig::F_DCL_REF_ID);
        $multiinput->addInput($ref_id_item);

        $table_id_item = new ilTextInputGUI('Datacollection Table-ID', srPHBernArbeitenarchivImporterConfig::F_DCL_TABLE_ID);
        $multiinput->addInput($table_id_item);

        $import_file = new ilTextInputGUI('Import-File', srPHBernArbeitenarchivImporterConfig::F_DCL_IMPORT_FILE);
        $multiinput->addInput($import_file);
        
        $remove_old_entries = new ilCheckboxInputGUI('Remove old entries', srPHBernArbeitenarchivImporterConfig::F_DCL_REMOVE_OLD_ENTRIES);
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
            $array[$key] = srPHBernArbeitenarchivImporterConfig::get($key);
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

            srPHBernArbeitenarchivImporterConfig::set($key, $this->getInput($key));
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