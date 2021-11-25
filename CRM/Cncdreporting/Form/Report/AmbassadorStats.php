<?php
use CRM_Cncdreporting_ExtensionUtil as E;

class CRM_Cncdreporting_Form_Report_AmbassadorStats extends CRM_Report_Form {
  public function __construct() {
    $this->_columns = [
      'civicrm_dummy_entity' => [
        'fields' => $this->getReportColumns(),
        'filters' => $this->getReportFilters(),
      ],
    ];

    parent::__construct();
  }

  private function getReportColumns() {
    $cols = [];

    $colTitles = [
      'Ambassadeur',
      'Nombre de SEPA rue',
      'Nombre de SEPA réel',
      'Age moyen',
      '% moins de 25 ans',
      'Nombre de completed avant FIRST',
      '% Completed avant FIRST',
      'Nombre de completed',
      '% Completed',
    ];

    $i = 1;
    foreach ($colTitles as $colTitle) {
      $cols["column$i"] = [
        'title' => $colTitle,
        'required' => TRUE,
        'dbAlias' => '1',
      ];

      $i++;
    }

    return $cols;
  }

  private function getReportFilters() {
    $filters = [
      'signature_date' => [
        'title' => 'Date de signature',
        'dbAlias' => '1',
        'type' => CRM_Utils_Type::T_DATE,
        'operatorType' => CRM_Report_Form::OP_DATE,
        'default' => 'previous.week',
      ],
    ];

    return $filters;
  }

  public function preProcess() {
    $this->assign('reportTitle', 'Statistiques ambassadeurs CNCD');
    parent::preProcess();
  }

  public function from() {
    // take small table
    $this->_from = "FROM  civicrm_domain {$this->_aliases['civicrm_contact']} ";
  }

  public function selectClause(&$tableName, $tableKey, &$fieldName, &$field) {
    return parent::selectClause($tableName, $tableKey, $fieldName, $field);
  }

  public function whereClause(&$field, $op, $value, $min, $max) {
    return '';
  }

  public function alterDisplay(&$rows) {
    // build the report from scratch

    [$dateFrom, $dateTo] = $this->getSignatureDateFilterFromTo();

    $rows = [];

    $ambassador = new CRM_Cncdreporting_Ambassador();
    $ambassadorNames = $ambassador->getAllAmbassadors();
    foreach ($ambassadorNames as $ambassadorName) {
      $row = [];

      $row['civicrm_dummy_entity_column1'] = $ambassadorName;
      $row['civicrm_dummy_entity_column2'] = $ambassador->getStatSepaStreet($ambassadorName, $dateFrom, $dateTo);
      $row['civicrm_dummy_entity_column3'] = $ambassador->getStatSepaReal($ambassadorName, $dateFrom, $dateTo);
      $row['civicrm_dummy_entity_column4'] = $ambassador->getStatSepaAverageAge($ambassadorName, $dateFrom, $dateTo);
      $row['civicrm_dummy_entity_column5'] = $ambassador->getStatSepaRealMinus25($ambassadorName, $dateFrom, $dateTo);
      $row['civicrm_dummy_entity_column6'] = '';
      $row['civicrm_dummy_entity_column7'] = '';
      $row['civicrm_dummy_entity_column8'] = '';
      $row['civicrm_dummy_entity_column9'] = '';

      $rows[] = $row;
    }
  }

  function getSignatureDateFilterFromTo() {
    $dateRelative = CRM_Utils_Array::value("signature_date_relative", $this->_params);
    $dateFrom = CRM_Utils_Array::value("signature_date_from", $this->_params);
    $dateTo = CRM_Utils_Array::value("signature_date_to", $this->_params);

    return CRM_Utils_Date::getFromTo($dateRelative, $dateFrom, $dateTo);
  }

}
