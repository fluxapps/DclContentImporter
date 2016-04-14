<#1>
<?php
	require_once('./Services/ActiveRecord/Fields/Converter/class.arBuilder.php');
	require_once('./Customizing/global/plugins/Services/Cron/CronHook/DclContentImporter/classes/Config/class.srDclContentImporterConfig.php');
	srDclContentImporterConfig::installDB();
	//$arBuilder = new arBuilder(new srDclContentImporterConfig());
    //$arBuilder->generateDBUpdateForInstallation();
?>