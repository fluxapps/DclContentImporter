<?php
include_once("./Services/Cron/classes/class.ilCronHookPlugin.php");
require_once(dirname(__FILE__) . '/Crons/class.srPHBernArbeitenarchivImporterCron.php');
require_once(dirname(__FILE__) . '/Config/class.srPHBernArbeitenarchivImporterConfig.php');

require_once('./Services/ActiveRecord/class.ActiveRecord.php');

/**
 * Class ilPHBernArbeitenarchivImporterPlugin
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 */
class ilPHBernArbeitenarchivImporterPlugin extends ilCronHookPlugin
{

    /**
     * @var ilPHBernArbeitenarchivImporterPlugin
     */
    protected static $instance;

    /**
     * @var array
     */
    protected static $cron_instances;


    /**
     * @return ilPHBernArbeitenarchivImporterPlugin
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * @return array
     */
    public static function getCronInstances()
    {
        if (self::$cron_instances === null) {
            self::$cron_instances = array(
                srPHBernArbeitenarchivImporterCron::CRON_ID => new srPHBernArbeitenarchivImporterCron()
            );
        }

        return self::$cron_instances;
    }


    /**
     * @param $key
     * @return string
     */
    public function getConfig($key)
    {
        return srPHBernArbeitenarchivImporterConfig::get($key);
    }


    /**
     * @return array
     */
    public function getCronJobInstances()
    {
        return self::getCronInstances();
    }


    /**
     * @param $a_job_id
     *
     * @return srPHBernArbeitenarchivImporterCron
     */
    public function getCronJobInstance($a_job_id)
    {
        foreach (self::getCronInstances() as $id => $cron) {
            if ($a_job_id == $id) {
                return $cron;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getPluginName()
    {
        return 'PHBernArbeitenarchivImporter';
    }

}