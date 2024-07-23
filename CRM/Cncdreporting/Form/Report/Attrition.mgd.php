<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
return [
  [
    'name' => 'CRM_Cncdreporting_Form_Report_Attrition',
    'entity' => 'ReportTemplate',
    'params' => [
      'version' => 3,
      'label' => 'Attrition',
      'description' => 'Attrition (be.cncd.cncdreporting)',
      'class_name' => 'CRM_Cncdreporting_Form_Report_Attrition',
      'report_url' => 'be.cncd.cncdreporting/attrition',
      'component' => '',
    ],
  ],
];
