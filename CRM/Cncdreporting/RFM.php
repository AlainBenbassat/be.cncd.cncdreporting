<?php

class CRM_Cncdreporting_RFM {
  public function getCategoryCodes() {
    return [
      'new' => 'NRG New',
      '001' => 'NRG 001',
      '010' => 'NRG 010',
      '011' => 'NRG 011',
      '100' => 'NRG 100',
      '101' => 'NRG 101',
      '110' => 'NRG 110',
      '111' => 'NRG 111',
    ];
  }

  public function getSelect() {
    return "
      contact_a.id as contact_id,
      contact_a.sort_name as sort_name,
      e.email as email,
      a.postal_code as postal_code,
      a.city as city,
      sum(contrib.total_amount) sum_contribs,
	    count(contrib.id) num_contribs,
	    round(avg(contrib.total_amount), 2) avg_contribs
    ";
  }

  public function getFrom() {
    return "
        civicrm_contact contact_a
      INNER JOIN
        civicrm_contribution contrib ON contrib.contact_id = contact_a.id
      LEFT JOIN
        civicrm_address a ON a.contact_id = contact_a.id AND a.is_primary = 1
      LEFT JOIN
        civicrm_email e ON e.contact_id = contact_a.id AND e.is_primary = 1
    ";
  }

  public function getWhere($referenceYear, $code) {
    return $this->getWhereContributionType()
      . $this->getWhereCode($referenceYear, $code);
  }

  public function getGroupBy() {
    return ' contact_a.id';
  }

  private function getWhereContributionType($subqueryId = '') {
    // FINANCIAL_TYPE_DON = 1;
    // FINANCIAL_TYPE_DON_NON_DEDUCTIBLE = 19;
    // FINANCIAL_TYPE_DON_PONCTUEL = 15;
    // FINANCIAL_TYPE_DON_POUR_CAMPAGNE = 3;
    // FINANCIAL_TYPE_PARAINAGE = 17;
    return " contrib$subqueryId.financial_type_id in (1, 19, 15, 3, 17) and contrib$subqueryId.contribution_status_id = 1 ";
  }

  private function getSubqueryContrib($numYearsAgo, $referenceYear) {
    $y = $referenceYear - $numYearsAgo;

    return "(
      select
        *
      from
        civicrm_contribution contrib$numYearsAgo
      where
        year(contrib$numYearsAgo.receive_date) = $y
      and
        contrib$numYearsAgo.contact_id = contact_a.id and
    " . $this->getWhereContributionType($numYearsAgo) . ') ';
  }

  private function getWhereCode($referenceYear, $code) {
    $where = '';
    $lastYear = $referenceYear - 1;
    $threeYearsAgo = $referenceYear - 3;

    switch ($code) {
      case 'new':
        $where = "and year(contrib.receive_date) = $referenceYear";
        break;
      case '001':
        $where = "and year(contrib.receive_date) between $threeYearsAgo and $lastYear";
        $where .= ' and exists ' . $this->getSubqueryContrib(1, $referenceYear);
        $where .= ' and not exists ' . $this->getSubqueryContrib(2, $referenceYear);
        $where .= ' and not exists ' . $this->getSubqueryContrib(3, $referenceYear);
        break;
      case '010';
        $where = "and year(contrib.receive_date) between $threeYearsAgo and $lastYear";
        $where .= ' and not exists ' . $this->getSubqueryContrib(1, $referenceYear);
        $where .= ' and exists ' . $this->getSubqueryContrib(2, $referenceYear);
        $where .= ' and not exists ' . $this->getSubqueryContrib(3, $referenceYear);
        break;
      case '011';
        $where = "and year(contrib.receive_date) between $threeYearsAgo and $lastYear";
        $where .= ' and exists ' . $this->getSubqueryContrib(1, $referenceYear);
        $where .= ' and exists ' . $this->getSubqueryContrib(2, $referenceYear);
        $where .= ' and not exists ' . $this->getSubqueryContrib(3, $referenceYear);
        break;
      case '100';
        $where = "and year(contrib.receive_date) between $threeYearsAgo and $lastYear";
        $where .= ' and not exists ' . $this->getSubqueryContrib(1, $referenceYear);
        $where .= ' and not exists ' . $this->getSubqueryContrib(2, $referenceYear);
        $where .= ' and exists ' . $this->getSubqueryContrib(3, $referenceYear);
        break;
      case '101';
        $where = "and year(contrib.receive_date) between $threeYearsAgo and $lastYear";
        $where .= ' and exists ' . $this->getSubqueryContrib(1, $referenceYear);
        $where .= ' and not exists ' . $this->getSubqueryContrib(2, $referenceYear);
        $where .= ' and exists ' . $this->getSubqueryContrib(3, $referenceYear);
        break;
      case '110';
        $where = "and year(contrib.receive_date) between $threeYearsAgo and $lastYear";
        $where .= ' and not exists ' . $this->getSubqueryContrib(1, $referenceYear);
        $where .= ' and exists ' . $this->getSubqueryContrib(2, $referenceYear);
        $where .= ' and exists ' . $this->getSubqueryContrib(3, $referenceYear);
        break;
      case '111';
        $where = "and year(contrib.receive_date) between $threeYearsAgo and $lastYear";
        $where .= ' and exists ' . $this->getSubqueryContrib(1, $referenceYear);
        $where .= ' and exists ' . $this->getSubqueryContrib(2, $referenceYear);
        $where .= ' and exists ' . $this->getSubqueryContrib(3, $referenceYear);
        break;
    }

    return $where;
  }

  public function getSummaryCount($referenceYear, $categoryCode) {
    $sql = 'select count(distinct contact_a.id) from '
      . $this->getFrom()
      . ' where ' . $this->getWhere($referenceYear, $categoryCode);

    return CRM_Core_DAO::singleValueQuery($sql);
  }
}
