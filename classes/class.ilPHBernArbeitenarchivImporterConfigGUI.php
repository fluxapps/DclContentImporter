<?php

require_once('./Services/Component/classes/class.ilPluginConfigGUI.php');
require_once('./Customizing/global/plugins/Services/Cron/CronHook/PHBernArbeitenarchivImporter/classes/Config/class.srPHBernArbeitenarchivImporterConfig.php');
require_once('./Customizing/global/plugins/Services/Cron/CronHook/PHBernArbeitenarchivImporter/classes/Config/class.srPHBernArbeitenarchivImporterConfigFormGUI.php');
require_once('class.ilPHBernArbeitenarchivImporterPlugin.php');
require_once('./Services/UIComponent/Button/classes/class.ilSubmitButton.php');

/**
 * Class ilPHBernArbeitenarchivImporterConfigGUI
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 */
class ilPHBernArbeitenarchivImporterConfigGUI extends ilPluginConfigGUI
{

    /**
     * @var ilPHBernArbeitenarchivImporterPlugin
     */
    protected $pl;

    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilTemplate
     */
    protected $tpl;


    public function __construct()
    {
        global $ilCtrl, $tpl;
        $this->pl = ilPHBernArbeitenarchivImporterPlugin::getInstance();
        $this->ctrl = $ilCtrl;
        $this->tpl = $tpl;
    }


    /**
     * @param $cmd
     */
    public function performCommand($cmd)
    {
        switch ($cmd) {
            case 'configure':
            case 'save':
                $this->$cmd();
                break;
        }
    }

    /**
     * Configure screen
     */
    public function configure()
    {
        global $ilToolbar;

        /** @var $ilToolbar ilToolbarGUI */
        $ilToolbar->setFormAction($this->ctrl->getFormAction($this));

        $form = new srPHBernArbeitenarchivImporterConfigFormGUI($this);
        $form->fillForm();
        $this->tpl->setContent($form->getHTML());
    }


    /**
     * Save config
     */
    public function save()
    {
        $form = new srPHBernArbeitenarchivImporterConfigFormGUI($this);
        if ($form->saveObject()) {
            ilUtil::sendSuccess('Saved Config', true);
            $this->ctrl->redirect($this, 'configure');
        } else {
            $form->setValuesByPost();
            $this->tpl->setContent($form->getHTML());
        }
    }
}