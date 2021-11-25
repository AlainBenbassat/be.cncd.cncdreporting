<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
return [
  [
    'name' => 'CRM_Cncdreporting_Form_Report_AmbassadorStats',
    'entity' => 'ReportTemplate',
    'params' => [
      'version' => 3,
      'label' => 'AmbassadorStats',
      'description' => 'AmbassadorStats (be.cncd.cncdreporting)',
      'class_name' => 'CRM_Cncdreporting_Form_Report_AmbassadorStats',
      'report_url' => 'be.cncd.cncdreporting/ambassadorstats',
      'component' => '',
    ],
  ],
];
