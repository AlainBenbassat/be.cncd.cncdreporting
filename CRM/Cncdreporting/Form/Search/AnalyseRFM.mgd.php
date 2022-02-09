<?php
// This file declares a managed database record of type "CustomSearch".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_managed
return [
  [
    'name' => 'CRM_Cncdreporting_Form_Search_AnalyseRFM',
    'entity' => 'CustomSearch',
    'params' => [
      'version' => 3,
      'label' => 'AnalyseRFM',
      'description' => 'AnalyseRFM (be.cncd.cncdreporting)',
      'class_name' => 'CRM_Cncdreporting_Form_Search_AnalyseRFM',
    ],
  ],
];
