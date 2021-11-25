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
      'name' => 'Ambassadeur',
      'num_sepa_street' => 'Nombre de SEPA rue',
      'num_sepa_real' => 'Nombre de SEPA réel',
      'num_sepa_under_10' => 'Nombre de SEPA réel -10€',
      'num_sepa_10' => 'Nombre de SEPA réel 10€',
      'num_sepa_above_10' => 'Nombre de SEPA réel +10€',
      'avg_age' => 'Age moyen',
      'pct_under_25' => '% moins de 25 ans',
      'num_completed_before_first' => 'Nombre de completed avant FIRST',
      'pct_completed_before_first' => '% Completed avant FIRST',
      'num_completed' => 'Nombre de completed',
      'pct_completed' => '% Completed',
    ];

    $i = 1;
    foreach ($colTitles as $k => $colTitle) {
      $cols[$k] = [
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

      $row['civicrm_dummy_entity_name'] = $ambassadorName;
      $row['civicrm_dummy_entity_num_sepa_street'] = $ambassador->getStatSepaStreet($ambassadorName, $dateFrom, $dateTo);

      $numSepaReal = $ambassador->getStatSepaReal($ambassadorName, $dateFrom, $dateTo);
      $row['civicrm_dummy_entity_num_sepa_real'] = $numSepaReal;

      $numSepaRealUnder10 = $ambassador->getStatSepaRealWithValue($ambassadorName, $dateFrom, $dateTo, ' < 10 ');
      $row['civicrm_dummy_entity_num_sepa_under_10'] = $this->calculateValueAndPercentage($numSepaRealUnder10, $numSepaReal);

      $numSepaReal10 = $ambassador->getStatSepaRealWithValue($ambassadorName, $dateFrom, $dateTo, ' = 10 ');
      $row['civicrm_dummy_entity_num_sepa_under_10'] = $this->calculateValueAndPercentage($numSepaReal10, $numSepaReal);

      $numSepaRealAbove10 = $ambassador->getStatSepaRealWithValue($ambassadorName, $dateFrom, $dateTo, ' > 10 ');
      $row['civicrm_dummy_entity_num_sepa_under_10'] = $this->calculateValueAndPercentage($numSepaRealAbove10, $numSepaReal);

      $row['civicrm_dummy_entity_avg_age'] = $ambassador->getStatSepaAverageAge($ambassadorName, $dateFrom, $dateTo);

      $numSepaRealMinus25 = $ambassador->getStatSepaRealMinus25($ambassadorName, $dateFrom, $dateTo);
      $row['civicrm_dummy_entity_pct_under_25'] = $this->calculatePercentage($numSepaRealMinus25, $numSepaReal);

      $numSepaCompletedBeforeFirst = $ambassador->getStatSepaCompletedBeforeFirst($ambassadorName, $dateFrom, $dateTo);
      $row['civicrm_dummy_entity_num_completed_before_first'] = $numSepaCompletedBeforeFirst;
      $row['civicrm_dummy_entity_pct_completed_before_first'] = $this->calculatePercentage($numSepaCompletedBeforeFirst, $numSepaReal);

      $numSepaCompleted = $ambassador->getStatSepaCompleted($ambassadorName, $dateFrom, $dateTo);
      $row['civicrm_dummy_entity_num_completed'] = $numSepaCompleted;
      $row['civicrm_dummy_entity_pct_completed'] = $this->calculatePercentage($numSepaCompleted, $numSepaReal);

      $rows[] = $row;
    }

    $row = [];

    $row['civicrm_dummy_entity_name'] = 'TOTAL';
    $row['civicrm_dummy_entity_num_sepa_street'] = $ambassador->getStatSepaStreet('', $dateFrom, $dateTo);

    $numSepaReal = $ambassador->getStatSepaReal('', $dateFrom, $dateTo);
    $row['civicrm_dummy_entity_num_sepa_real'] = $numSepaReal;

    $numSepaRealUnder10 = $ambassador->getStatSepaRealWithValue('', $dateFrom, $dateTo, ' < 10 ');
    $row['civicrm_dummy_entity_num_sepa_under_10'] = $this->calculateValueAndPercentage($numSepaRealUnder10, $numSepaReal);

    $numSepaReal10 = $ambassador->getStatSepaRealWithValue('', $dateFrom, $dateTo, ' = 10 ');
    $row['civicrm_dummy_entity_num_sepa_under_10'] = $this->calculateValueAndPercentage($numSepaReal10, $numSepaReal);

    $numSepaRealAbove10 = $ambassador->getStatSepaRealWithValue('', $dateFrom, $dateTo, ' > 10 ');
    $row['civicrm_dummy_entity_num_sepa_under_10'] = $this->calculateValueAndPercentage($numSepaRealAbove10, $numSepaReal);

    $row['civicrm_dummy_entity_avg_age'] = $ambassador->getStatSepaAverageAge('', $dateFrom, $dateTo);

    $numSepaRealMinus25 = $ambassador->getStatSepaRealMinus25('', $dateFrom, $dateTo);
    $row['civicrm_dummy_entity_pct_under_25'] = $this->calculatePercentage($numSepaRealMinus25, $numSepaReal);

    $numSepaCompletedBeforeFirst = $ambassador->getStatSepaCompletedBeforeFirst('', $dateFrom, $dateTo);
    $row['civicrm_dummy_entity_num_completed_before_first'] = $numSepaCompletedBeforeFirst;
    $row['civicrm_dummy_entity_pct_completed_before_first'] = $this->calculatePercentage($numSepaCompletedBeforeFirst, $numSepaReal);

    $numSepaCompleted = $ambassador->getStatSepaCompleted('', $dateFrom, $dateTo);
    $row['civicrm_dummy_entity_num_completed'] = $numSepaCompleted;
    $row['civicrm_dummy_entity_pct_completed'] = $this->calculatePercentage('', $numSepaReal);
    $this->makeBold($row);

    $rows[] = $row;
  }

  private function makeBold(&$row) {
    foreach ($row as $k => $v) {
      $row[$k] = "<b>$v</b>";
    }
  }

  private function calculatePercentage($numSegment, $numTotal) {
    if ($numTotal) {
      return round($numSegment / $numTotal * 100, 1) . '&nbsp;%';
    }
    else {
      return '';
    }
  }

  private function calculateValueAndPercentage($numSegment, $numTotal) {
    if ($numTotal) {
      return $numSegment . ' (' . $this->calculatePercentage($numSegment, $numTotal) . ')';
    }
    else {
      return '';
    }
  }

  function getSignatureDateFilterFromTo() {
    $dateRelative = CRM_Utils_Array::value("signature_date_relative", $this->_params);
    $dateFrom = CRM_Utils_Array::value("signature_date_from", $this->_params);
    $dateTo = CRM_Utils_Array::value("signature_date_to", $this->_params);

    return CRM_Utils_Date::getFromTo($dateRelative, $dateFrom, $dateTo);
  }

}
