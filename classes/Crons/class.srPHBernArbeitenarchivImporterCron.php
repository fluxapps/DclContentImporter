<?php

require_once './Services/Cron/classes/class.ilCronJob.php';
require_once './Services/Cron/classes/class.ilCronJobResult.php';
require_once(dirname(dirname(__FILE__)) . '/class.ilPHBernArbeitenarchivImporterPlugin.php');

require_once ('./Modules/DataCollection/classes/class.ilObjDataCollection.php');

/**
 * Class srPHBernArbeitenarchivImporterCron
 *
 * @author Michael Herren
 */
class srPHBernArbeitenarchivImporterCron extends ilCronJob
{

    const CRON_ID = 'sr_phbern_arbeitenarchiv_importer';
    const CSV_DELIMITER = ";";

    /**
     * @var  ilPHBernArbeitenarchivImporterPlugin
     */
    protected $pl;
    /**
     * @var  ilDB
     */
    protected $db;
    /**
     * @var  ilLog
     */
    protected $ilLog;


    public function __construct()
    {
        global $ilDB, $ilLog;
        $this->db = $ilDB;
        $this->pl = ilPHBernArbeitenarchivImporterPlugin::getInstance();
        $this->log = $ilLog;
    }


    public function getTitle()
    {
        return "VSPH imports for Arbeitenarchiv";
    }


    public function getDescription()
    {
        return "Load data for Arbeitenarchiv-DataCollection";
    }


    /**
     * @return string
     */
    public function getId()
    {
        return self::CRON_ID;
    }


    /**
     * @return bool
     */
    public function hasAutoActivation()
    {
        return true;
    }


    /**
     * @return bool
     */
    public function hasFlexibleSchedule()
    {
        return true;
    }

    /**
     * @return int
     */
    public function getDefaultScheduleType()
    {
        return self::SCHEDULE_TYPE_WEEKLY;
    }

    /**
     * @return array|int
     */
    public function getDefaultScheduleValue()
    {
        return 1;
    }


    /**
     * @return ilCronJobResult
     */
    public function run()
    {
        global $ilLog;
        $collections = $this->pl->getConfig(srPHBernArbeitenarchivImporterConfig::F_DCL_CONFIG);

        $result = new ilCronJobResult();
        if(is_array($collections) && count($collections) > 0) {
            foreach($collections as $collection) {
                $this->importFile($collection);
            }
            $result->setStatus(ilCronJobResult::STATUS_OK);
        } else {
            $result->setStatus(ilCronJobResult::STATUS_INVALID_CONFIGURATION);
        }

        return $result;
    }

    protected function importFile($collection) {
        global $ilUser;

        // get properties
        $file = $collection[srPHBernArbeitenarchivImporterConfig::F_DCL_IMPORT_FILE];
        $data_collection = $collection[srPHBernArbeitenarchivImporterConfig::F_DCL_REF_ID];
        $table_id = $collection[srPHBernArbeitenarchivImporterConfig::F_DCL_TABLE_ID];
        $delete_old_entries = $collection[srPHBernArbeitenarchivImporterConfig::F_DCL_REMOVE_OLD_ENTRIES];

        ilDclCache::resetCache();

        if(file_exists($file)) {
            $fd = fopen ($file, "r");
            $parsed_csv = array();
            while (($data = fgetcsv($fd, 0, self::CSV_DELIMITER)) !== false)
            {
                // remove BOM in the beginning of a file
                $line = array_map('trim', $data, array(pack("CCC", 0xef, 0xbb, 0xbf)));
                $line = array_filter($line);

                if(count($line) > 0) {
                    $parsed_csv[] = $line;
                }
            }
            fclose ($fd);

            $dcl = new ilObjDataCollection($data_collection);
            $tables = $dcl->getTables();
            $user = $ilUser;

            if(isset($tables[$table_id])) {
                /** @var ilDclTable $table */
                $table = $tables[$table_id];
                $mappings = array();
                $id_field_key = 'VSPH-UniqueID';

                if(count($parsed_csv) > 0) {
                    // parse title-column
                    foreach($parsed_csv[0] as $field_key=>$field_title) {
                        // cast unique-ids
                        //TODO: make more dynamic
                        if(strtolower($field_title) == "#uniqueid") {
                            $field_title = $id_field_key;
                        }
                        $mapping_field = $table->getFieldByTitle($field_title);
                        if($mapping_field == null) {
                            throw new Exception("Field not found for '".$field_title."' in file ".$file);
                        }

                        $mappings[$field_key] = $mapping_field;
                    }
                    unset($parsed_csv[0]);

                    $records = $table->getRecords();
                    $available_records = array();
                    foreach($parsed_csv as $line) {
                        if(count($line) != count($mappings)) {
                            throw new Exception("Number of elements in line ".print_r($line, true)." does not match!");
                        }

                        $update_record_id = 0;
                        foreach($records as $record_key=>$record) {
                            $value = $record->getRecordFieldValue($mappings[0]->getId());

                            if($value == $line[0]) {
                                $available_records[$record_key] = $record;
                                $update_record_id = $record->getId();
                                break;
                            }
                        }

                        $record_obj = ilDclCache::getRecordCache($update_record_id);

                        $date_obj = new ilDateTime(time(), IL_CAL_UNIX);
                        if($update_record_id == 0) {
                            $record_obj->setTableId($table_id);
                            $record_obj->setLastEditBy($user->getId());
                            $record_obj->setOwner($user->getId());
                            $record_obj->setCreateDate($date_obj->get(IL_CAL_DATETIME));
                            $record_obj->doCreate();
                        }
                        $record_obj->setLastUpdate($date_obj->get(IL_CAL_DATETIME));

                        foreach($line as $field_key => $field_value) {
                            /** @var ilDclBaseFieldModel $field */
                            $field = $mappings[$field_key];
                            if($field == null) {
                                throw new Exception("Field with key ".$field_key." for value ".$field_value." was not found!");
                            }

                            $record_obj->setRecordFieldValue($field->getId(), $field_value);
                        }
                        $record_obj->doUpdate();
                    }

                    // delete records
                    if($delete_old_entries) {
                        // remove old records
                        $records_to_delete = array_diff_key($records, $available_records);
                        /**
                         * @var ilDclBaseRecordModel $delete_record
                         */
                        foreach($records_to_delete as $delete_record) {
                            $delete_record->doDelete();
                        }
                    }
                }
            }
        }
    }
}