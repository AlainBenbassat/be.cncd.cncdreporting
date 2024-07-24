<?php
use CRM_Cncdreporting_ExtensionUtil as E;

class CRM_Cncdreporting_Form_Report_Attrition extends CRM_Report_Form {
  private $ambassador;

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

    $this->ambassador = new CRM_Cncdreporting_Ambassador();

    parent::__construct();
  }

  private function getReportColumns() {
    $cols = [];

    $colTitles = [
      'name' => 'Ambassadeur',
      'ref_month_num_sepa' => 'Nombre de SEPA',
      'ref_month_expected_amount' => 'Montant mensuel attendu (€)',
      'month_0_pct_cancelled' => '% Annulations à N',
      'month_0_total_received' => 'Montant mensuel reçu à N',
      'month_1_num_active' => 'Nombre SEPA actif à N+1',
      'month_1_pct_cancelled' => '% Annulations à N+1',
      'month_1_total_received' => 'Montant mensuel reçu à N+1',
      'month_1_evolution_total_received' => 'Evolution Montant reçu à N+1',
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

    $ambassadorNames = $this->ambassador->getAllAmbassadors();
    foreach ($ambassadorNames as $ambassadorName) {
      $num = $this->ambassador->getStatSepaStreet($ambassadorName, $refDateFrom, $refDateTo);
      if ($num == 0) {
        continue;
      }

      $row = [];
      $row['civicrm_dummy_entity_name'] = $ambassadorName;

      $row['civicrm_dummy_entity_ref_month_num_sepa'] = $num;
      $row['civicrm_dummy_entity_ref_month_expected_amount'] = $this->ambassador->getStatSepaStreetSum($ambassadorName, $refDateFrom, $refDateTo);

      $this->calcRow($row, 'month_0', $ambassadorName, $refDateFrom, $refDateTo, $refDateFrom . ' 00:00:00', $refDateTo . ' 23:59:59');
      $this->calcRow($row, 'month_1', $ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth1, $toDateContribsMonth1);
      $this->calcRow($row, 'month_2', $ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth2, $toDateContribsMonth2);
      $this->calcRow($row, 'month_3', $ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth3, $toDateContribsMonth3);
      $this->calcRow($row, 'month_6', $ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth6, $toDateContribsMonth6);
      $this->calcRow($row, 'month_12', $ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth12, $toDateContribsMonth12);
      $this->calcRow($row, 'month_24', $ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth24, $toDateContribsMonth24);

      $rows[] = $row;
    }

    // TOTALS
    $totalRow = [];
    $this->addTotals($rows, $totalRow);
    $this->makeBold($totalRow);

    $rows[] = $totalRow;
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

  private function calcRow(&$row, $prefix, $ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth, $toDateContribsMonth) {
    $row["civicrm_dummy_entity_{$prefix}_num_active"] = $this->ambassador->getStatSepaStreetStillActive($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth, $toDateContribsMonth);
    $row["civicrm_dummy_entity_{$prefix}_pct_cancelled"] = round($this->ambassador->getStatSepaStreetCancelledInPeriod($ambassadorName, $refDateFrom, $refDateTo, $refDateFrom, $toDateContribsMonth) / $row['civicrm_dummy_entity_ref_month_num_sepa'] * 100, 1) . ' %';
    $row["civicrm_dummy_entity_{$prefix}_total_received"] = $this->ambassador->getStatSepaStreetSumContribs($ambassadorName, $refDateFrom, $refDateTo, $fromDateContribsMonth, $toDateContribsMonth);
    $row["civicrm_dummy_entity_{$prefix}_evolution_total_received"] = '-' . round(($row["civicrm_dummy_entity_ref_month_expected_amount"] - $row["civicrm_dummy_entity_{$prefix}_total_received"]) / $row['civicrm_dummy_entity_ref_month_expected_amount'] * 100, 1) . ' %';

    if ($prefix != 'month_1') {
      $row["civicrm_dummy_entity_{$prefix}_cumul_total_received"] = $this->ambassador->getStatSepaStreetSumContribs($ambassadorName, $refDateFrom, $refDateTo, $refDateFrom, $toDateContribsMonth);
    }
  }

  private function addTotals($rows, &$totalRow) {
    foreach ($rows as $row) {
      foreach ($row as $k => $v) {
        switch ($k) {
          case 'civicrm_dummy_entity_name':
            $totalRow[$k] = 'TOTAL';
            break;
          case 'civicrm_dummy_entity_ref_month_num_sepa':
          case 'civicrm_dummy_entity_ref_month_expected_amount':
          case 'civicrm_dummy_entity_month_1_num_active':
          case 'civicrm_dummy_entity_month_2_num_active':
          case 'civicrm_dummy_entity_month_3_num_active':
          case 'civicrm_dummy_entity_month_6_num_active':
          case 'civicrm_dummy_entity_month_12_num_active':
          case 'civicrm_dummy_entity_month_24_num_active':
          case 'civicrm_dummy_entity_month_1_total_received':
          case 'civicrm_dummy_entity_month_2_total_received':
          case 'civicrm_dummy_entity_month_3_total_received':
          case 'civicrm_dummy_entity_month_6_total_received':
          case 'civicrm_dummy_entity_month_12_total_received':
          case 'civicrm_dummy_entity_month_24_total_received':
          case 'civicrm_dummy_entity_month_2_cumul_total_received':
          case 'civicrm_dummy_entity_month_3_cumul_total_received':
          case 'civicrm_dummy_entity_month_6_cumul_total_received':
          case 'civicrm_dummy_entity_month_12_cumul_total_received':
          case 'civicrm_dummy_entity_month_24_cumul_total_received':
            if ($v) {
              $totalRow[$k] = empty($totalRow[$k]) ? $v : $totalRow[$k] + $v;
            }
            break;
          default:
            $totalRow[$k] = '';
        }
      }
    }
  }
}
