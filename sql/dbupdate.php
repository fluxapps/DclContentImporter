<#1>
<?php
	require_once('./Services/ActiveRecord/Fields/Converter/class.arBuilder.php');
	require_once('./Customizing/global/plugins/Services/Cron/CronHook/PHBernArbeitenarchivImporter/classes/Config/class.srPHBernArbeitenarchivImporterConfig.php');

	srPHBernArbeitenarchivImporterConfig::installDB();
	//$arBuilder = new arBuilder(new srPHBernArbeitenarchivImporterConfig());
    //$arBuilder->generateDBUpdateForInstallation();
?>