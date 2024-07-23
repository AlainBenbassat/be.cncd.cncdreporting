<?php
use CRM_Cncdreporting_ExtensionUtil as E;

class CRM_Cncdreporting_Form_Report_Attrition extends CRM_Report_Form {
  private $months = [
    '01' => 'Janvier',
    '02' => 'Février',
    '03' => 'Mars',
    '04' => 'Avril',
    '05' => 'Mai',
    '06' => 'Juin',
    '07' => 'Juillet',
    '08' => 'Août',
    '09' => 'Septembre',
    '10' => 'Octobre',
    '11' => 'Novembre',
    '12' => 'Décembre',
  ];

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
      'ref_month_num_sepa' => 'Nombre de SEPA',
      'ref_month_expected_amount' => 'Montant mensuel attendu (€)',
      'month_1_num_active' => 'Nombre SEPA actif à N+1',
      'month_1_pct_cancelled' => '% Annulations à N+1',
      'month_1_total_received' => 'Montant mensuel reçu à N+1',
      'month_1_evolution_total_received' => 'Evolution Montant reçu à N+1',
      'month_1_cumul_total_received' => 'Cumul montant à N+1',
      'month_2_num_active' => 'Nombre SEPA actif à N+2',
      'month_2_pct_cancelled' => '% Annulations à N+2',
      'month_2_total_received' => 'Montant mensuel reçu à N+2',
      'month_2_evolution_total_received' => 'Evolution Montant reçu à N+2',
      'month_2_cumul_total_received' => 'Cumul montant à N+2',
      'month_3_num_active' => 'Nombre SEPA actif à N+3',
      'month_3_pct_cancelled' => '% Annulations à N+3',
      'month_3_total_received' => 'Montant mensuel reçu à N+3',
      'month_3_evolution_total_received' => 'Evolution Montant reçu à N+3',
      'month_3_cumul_total_received' => 'Cumul montant à N+3',
      'month_6_num_active' => 'Nombre SEPA actif à N+6',
      'month_6_pct_cancelled' => '% Annulations à N+6',
      'month_6_total_received' => 'Montant mensuel reçu à N+6',
      'month_6_evolution_total_received' => 'Evolution Montant reçu à N+6',
      'month_6_cumul_total_received' => 'Cumul montant à N+6',
      'month_12_num_active' => 'Nombre SEPA actif à N+12',
      'month_12_pct_cancelled' => '% Annulations à N+12',
      'month_12_total_received' => 'Montant mensuel reçu à N+12',
      'month_12_evolution_total_received' => 'Evolution Montant reçu à N+12',
      'month_12_cumul_total_received' => 'Cumul montant à N+12',
      'month_24_num_active' => 'Nombre SEPA actif à N+24',
      'month_24_pct_cancelled' => '% Annulations à N+24',
      'month_24_total_received' => 'Montant mensuel reçu à N+24',
      'month_24_evolution_total_received' => 'Evolution Montant reçu à N+24',
      'month_24_cumul_total_received' => 'Cumul montant à N+24',
    ];

    $i = 1;
    foreach ($colTitles as $k => $colTitle) {
      $cols[$k] = [
        'title' => $colTitle,
        'required' => FALSE,
        'default' => 1,
        'dbAlias' => '1',
      ];

      $i++;
    }

    return $cols;
  }

  private function getReportFilters() {
    $currentYear = intval(date('Y'));
    $toYear = $currentYear - 10;

    $years = [];
    for ($i = $currentYear; $i >= $toYear; $i--) {
      $years[$i] = $i;
    }

    $filters = [
      'signature_month' => [
        'title' => 'Mois de signature',
        'dbAlias' => '1',
        'type' => CRM_Utils_Type::T_INT,
        'operatorType' => CRM_Report_Form::OP_SELECT,
        'options' => $this->months,
        'default' => 1,
      ],
      'signature_year' => [
        'title' => 'Année de signature',
        'dbAlias' => '1',
        'type' => CRM_Utils_Type::T_INT,
        'operatorType' => CRM_Report_Form::OP_SELECT,
        'options' => $years,
        'default' => $currentYear,
      ],
    ];

    return $filters;
  }

  public function preProcess() {
    $this->assign('reportTitle', 'Attrition par ambassadeur');
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

    [$refMonth, $refYear] = $this->getSignatureDateFilters();
    $refDateFrom = "$refYear-$refMonth-01";
    $refDateTo = date('Y-m-t', strtotime($refDateFrom));

    $rows = [];

    $ambassador = new CRM_Cncdreporting_Ambassador();
    $ambassadorNames = $ambassador->getAllAmbassadors();
    foreach ($ambassadorNames as $ambassadorName) {
      $num = $ambassador->getStatSepaStreet($ambassadorName, $refDateFrom, $refDateTo);
      if ($num == 0) {
        continue;
      }

      $row = [];
      $row['civicrm_dummy_entity_name'] = $ambassadorName;

      $row['civicrm_dummy_entity_ref_month_num_sepa'] = $num;
      $row['civicrm_dummy_entity_ref_month_expected_amount'] = $ambassador->getStatSepaStreetSum($ambassadorName, $refDateFrom, $refDateTo);

      $fromDateContribs = $this->addNumMonths($refDateFrom, 1) . ' 00:00:00';
      $toDateContribs = $this->addNumMonths(date('Y-m-t', strtotime($refDateFrom)), 1) . ' 23:59:59';
      $row['civicrm_dummy_entity_month_1_num_active'] = '';
      $row['civicrm_dummy_entity_month_1_pct_cancelled'] = '';
      $row['civicrm_dummy_entity_month_1_total_received'] = $ambassador->getStatSepaStreetSumContribs($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribs, $toDateContribs);
      $row['civicrm_dummy_entity_month_1_evolution_total_received'] = '';
      $row['civicrm_dummy_entity_month_1_cumul_total_received'] = '';

      $fromDateContribs = $this->addNumMonths($refDateFrom, 2) . ' 00:00:00';
      $toDateContribs = $this->addNumMonths(date('Y-m-t', strtotime($refDateFrom)), 2) . ' 23:59:59';
      $row['civicrm_dummy_entity_month_2_num_active'] = '';
      $row['civicrm_dummy_entity_month_2_pct_cancelled'] = '';
      $row['civicrm_dummy_entity_month_2_total_received'] = $ambassador->getStatSepaStreetSumContribs($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribs, $toDateContribs);;
      $row['civicrm_dummy_entity_month_2_evolution_total_received'] = '';
      $row['civicrm_dummy_entity_month_2_cumul_total_received'] = '';

      $fromDateContribs = $this->addNumMonths($refDateFrom, 3) . ' 00:00:00';
      $toDateContribs = $this->addNumMonths(date('Y-m-t', strtotime($refDateFrom)), 3) . ' 23:59:59';
      $row['civicrm_dummy_entity_month_3_num_active'] = '';
      $row['civicrm_dummy_entity_month_3_pct_cancelled'] = '';
      $row['civicrm_dummy_entity_month_3_total_received'] = $ambassador->getStatSepaStreetSumContribs($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribs, $toDateContribs);;
      $row['civicrm_dummy_entity_month_3_evolution_total_received'] = '';
      $row['civicrm_dummy_entity_month_3_cumul_total_received'] = '';

      $fromDateContribs = $this->addNumMonths($refDateFrom, 6) . ' 00:00:00';
      $toDateContribs = $this->addNumMonths(date('Y-m-t', strtotime($refDateFrom)), 6) . ' 23:59:59';
      $row['civicrm_dummy_entity_month_6_num_active'] = '';
      $row['civicrm_dummy_entity_month_6_pct_cancelled'] = '';
      $row['civicrm_dummy_entity_month_6_total_received'] = $ambassador->getStatSepaStreetSumContribs($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribs, $toDateContribs);;
      $row['civicrm_dummy_entity_month_6_evolution_total_received'] = '';
      $row['civicrm_dummy_entity_month_6_cumul_total_received'] = '';

      $fromDateContribs = $this->addNumMonths($refDateFrom, 12) . ' 00:00:00';
      $toDateContribs = $this->addNumMonths(date('Y-m-t', strtotime($refDateFrom)), 12) . ' 23:59:59';
      $row['civicrm_dummy_entity_month_12_num_active'] = '';
      $row['civicrm_dummy_entity_month_12_pct_cancelled'] = '';
      $row['civicrm_dummy_entity_month_12_total_received'] = $ambassador->getStatSepaStreetSumContribs($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribs, $toDateContribs);;
      $row['civicrm_dummy_entity_month_12_evolution_total_received'] = '';
      $row['civicrm_dummy_entity_month_12_cumul_total_received'] = '';

      $fromDateContribs = $this->addNumMonths($refDateFrom, 24) . ' 00:00:00';
      $toDateContribs = $this->addNumMonths(date('Y-m-t', strtotime($refDateFrom)), 24) . ' 23:59:59';
      $row['civicrm_dummy_entity_month_24_num_active'] = '';
      $row['civicrm_dummy_entity_month_24_pct_cancelled'] = '';
      $row['civicrm_dummy_entity_month_24_total_received'] = $ambassador->getStatSepaStreetSumContribs($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribs, $toDateContribs);;
      $row['civicrm_dummy_entity_month_24_evolution_total_received'] = '';
      $row['civicrm_dummy_entity_month_24_cumul_total_received'] = '';

      $rows[] = $row;
    }

    // TOTALS
    $row = [];
    $row['civicrm_dummy_entity_name'] = 'TOTAL';

    $this->makeBold($row);

    $rows[] = $row;
  }

  private function makeBold(&$row) {
    foreach ($row as $k => $v) {
      $row[$k] = "<b>$v</b>";
    }
  }

  private function getSignatureDateFilters() {
    $values =  $this->exportValues();

    return [$values['signature_month_value'], $values['signature_year_value']];
  }

  private function addNumMonths($baseDate, $numMonths) {
    $date = date_create($baseDate);
    date_add($date, date_interval_create_from_date_string("$numMonths months"));
    return date_format($date,'Y-m-d');
  }
}
