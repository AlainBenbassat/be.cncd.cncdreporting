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
    // take small table, we will build the report from scratch in alterDisplay()
    $this->_from = "FROM  civicrm_domain {$this->_aliases['civicrm_contact']} ";
  }

  public function selectClause(&$tableName, $tableKey, &$fieldName, &$field) {
    return parent::selectClause($tableName, $tableKey, $fieldName, $field);
  }

  public function whereClause(&$field, $op, $value, $min, $max) {
    return '';
  }

  public function alterDisplay(&$rows) {
    [$refMonth, $refYear] = $this->getSignatureDateFilters();

    $tmpDate = "$refYear-$refMonth-01";
    [$refDateFrom, $refDateTo] = $this->convertDateToPeriod($tmpDate);

    $tmpDate = $this->addNumMonths($refDateFrom, 1);
    [$fromDateContribsMonth1, $toDateContribsMonth1] = $this->convertDateToPeriod($tmpDate);

    $tmpDate = $this->addNumMonths($refDateFrom, 2);
    [$fromDateContribsMonth2, $toDateContribsMonth2] = $this->convertDateToPeriod($tmpDate);

    $tmpDate = $this->addNumMonths($refDateFrom, 3);
    [$fromDateContribsMonth3, $toDateContribsMonth3] = $this->convertDateToPeriod($tmpDate);

    $tmpDate = $this->addNumMonths($refDateFrom, 6);
    [$fromDateContribsMonth6, $toDateContribsMonth6] = $this->convertDateToPeriod($tmpDate);

    $tmpDate = $this->addNumMonths($refDateFrom, 12);
    [$fromDateContribsMonth12, $toDateContribsMonth12] = $this->convertDateToPeriod($tmpDate);

    $tmpDate = $this->addNumMonths($refDateFrom, 24);
    [$fromDateContribsMonth24, $toDateContribsMonth24] = $this->convertDateToPeriod($tmpDate);

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

      $row['civicrm_dummy_entity_month_1_num_active'] = $ambassador->getStatSepaStreetStillActive($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth1, $toDateContribsMonth1);
      $row['civicrm_dummy_entity_month_1_pct_cancelled'] = $ambassador->getStatSepaStreetCancelledInPeriod($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth1, $toDateContribsMonth1);
      $row['civicrm_dummy_entity_month_1_total_received'] = $ambassador->getStatSepaStreetSumContribs($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth1, $toDateContribsMonth1);
      $row['civicrm_dummy_entity_month_1_evolution_total_received'] = '';
      $row['civicrm_dummy_entity_month_1_cumul_total_received'] = '';

      $row['civicrm_dummy_entity_month_2_num_active'] = $ambassador->getStatSepaStreetStillActive($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth2, $toDateContribsMonth2);
      $row['civicrm_dummy_entity_month_2_pct_cancelled'] = $ambassador->getStatSepaStreetCancelledInPeriod($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth2, $toDateContribsMonth2);
      $row['civicrm_dummy_entity_month_2_total_received'] = $ambassador->getStatSepaStreetSumContribs($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth2, $toDateContribsMonth2);
      $row['civicrm_dummy_entity_month_2_evolution_total_received'] = '';
      $row['civicrm_dummy_entity_month_2_cumul_total_received'] = $ambassador->getStatSepaStreetSumContribs($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth1, $toDateContribsMonth2);

      $row['civicrm_dummy_entity_month_3_num_active'] = $ambassador->getStatSepaStreetStillActive($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth3, $toDateContribsMonth3);
      $row['civicrm_dummy_entity_month_3_pct_cancelled'] = $ambassador->getStatSepaStreetCancelledInPeriod($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth3, $toDateContribsMonth3);
      $row['civicrm_dummy_entity_month_3_total_received'] = $ambassador->getStatSepaStreetSumContribs($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth3, $toDateContribsMonth3);
      $row['civicrm_dummy_entity_month_3_evolution_total_received'] = '';
      $row['civicrm_dummy_entity_month_3_cumul_total_received'] = $ambassador->getStatSepaStreetSumContribs($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth1, $toDateContribsMonth3);

      $row['civicrm_dummy_entity_month_6_num_active'] = $ambassador->getStatSepaStreetStillActive($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth6, $toDateContribsMonth6);
      $row['civicrm_dummy_entity_month_6_pct_cancelled'] = $ambassador->getStatSepaStreetCancelledInPeriod($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth6, $toDateContribsMonth6);
      $row['civicrm_dummy_entity_month_6_total_received'] = $ambassador->getStatSepaStreetSumContribs($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth6, $toDateContribsMonth6);
      $row['civicrm_dummy_entity_month_6_evolution_total_received'] = '';
      $row['civicrm_dummy_entity_month_6_cumul_total_received'] = $ambassador->getStatSepaStreetSumContribs($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth1, $toDateContribsMonth6);

      $row['civicrm_dummy_entity_month_12_num_active'] = $ambassador->getStatSepaStreetStillActive($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth12, $toDateContribsMonth12);
      $row['civicrm_dummy_entity_month_12_pct_cancelled'] = $ambassador->getStatSepaStreetCancelledInPeriod($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth12, $toDateContribsMonth12);
      $row['civicrm_dummy_entity_month_12_total_received'] = $ambassador->getStatSepaStreetSumContribs($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth12, $toDateContribsMonth12);
      $row['civicrm_dummy_entity_month_12_evolution_total_received'] = '';
      $row['civicrm_dummy_entity_month_12_cumul_total_received'] = $ambassador->getStatSepaStreetSumContribs($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth1, $toDateContribsMonth12);

      $row['civicrm_dummy_entity_month_24_num_active'] = $ambassador->getStatSepaStreetStillActive($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth24, $toDateContribsMonth24);
      $row['civicrm_dummy_entity_month_24_pct_cancelled'] = $ambassador->getStatSepaStreetCancelledInPeriod($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth24, $toDateContribsMonth24);
      $row['civicrm_dummy_entity_month_24_total_received'] = $ambassador->getStatSepaStreetSumContribs($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth24, $toDateContribsMonth24);
      $row['civicrm_dummy_entity_month_24_evolution_total_received'] = '';
      $row['civicrm_dummy_entity_month_24_cumul_total_received'] = $ambassador->getStatSepaStreetSumContribs($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth1, $toDateContribsMonth24);

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

  private function getSignatureDateFilters(): array {
    $values =  $this->exportValues();

    return [$values['signature_month_value'], $values['signature_year_value']];
  }

  private function addNumMonths(string $baseDate, string $numMonths): string {
    $date = date_create($baseDate);
    date_add($date, date_interval_create_from_date_string("$numMonths months"));
    return date_format($date,'Y-m-d');
  }

  private function convertDateToPeriod(string $baseDate): array {
    $from = $baseDate . ' 00:00:00';
    $to = date('Y-m-t', strtotime($from)) . ' 23:59:59'; // t = last day of the month

    return [$from, $to];
  }
}
