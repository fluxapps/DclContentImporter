<?php
require_once('./Services/ActiveRecord/class.ActiveRecord.php');

/**
 * Class srDclContentImporterConfig
 *
 * @author Michael Herren <mh@studer-raimann.ch>
 */
class srDclContentImporterConfig extends ActiveRecord
{
    const F_DCL_CONFIG = 'dcl_config';
    const F_DCL_REF_ID = 'dcl_ref_id';
    const F_DCL_TABLE_ID = 'dcl_table_id';
    const F_DCL_IMPORT_FILE = 'dcl_import_file';
    const F_DCL_REMOVE_OLD_ENTRIES = 'dcl_remove_old_entries';

    /**
     * @var array
     */
    protected static $cache = array();
    /**
     * @var array
     */
    protected static $cache_loaded = array();
    /**
     * @var bool
     */
    protected $ar_safe_read = false;
    /**
     * @var string
     *
     * @db_has_field        true
     * @db_is_unique        true
     * @db_is_primary       true
     * @db_is_notnull       true
     * @db_fieldtype        text
     * @db_length           128
     */
    protected $name;
    /**
     * @var string
     *
     * @db_has_field        true
     * @db_fieldtype        text
     * @db_length           1000
     */
    protected $value;


    /**
     * @param $name
     *
     * @return string
     */
    public static function getConfigValue($name)
    {
        if ( ! isset(self::$cache_loaded[$name])) {
            $obj = self::find($name);
            if ($obj === NULL) {
                self::$cache[$name] = NULL;
            } else {
                self::$cache[$name] = json_decode($obj->getValue(), true);
            }
            self::$cache_loaded[$name] = true;
        }
        return self::$cache[$name];
    }


    /**
     * @param $name
     * @param $value
     *
     * @return null
     */
    public static function set($name, $value)
    {
        /**
         * @var $obj arConfig
         */
        $obj = self::findOrGetInstance($name);
        $obj->setValue(json_encode($value));
        if (self::where(array('name' => $name))->hasSets()) {
            $obj->update();
        } else {
            $obj->create();
        }
    }


    public static function returnDbTableName()
    {
        return 'sr_dcl_importer_c';
    }


    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }


    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }


    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
} 