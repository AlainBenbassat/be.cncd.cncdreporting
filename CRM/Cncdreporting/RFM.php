<?php

class CRM_Cncdreporting_RFM {
  public function getCategoryCodes() {
    return [
      'NRG New',
      'NRG 001',
      'NRG 010',
      'NRG 011',
      'NRG 100',
      'NRG 101',
      'NRG 110',
      'NRG 111',
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
    $where = "year(contrib.receive_date) = $referenceYear";

    // TODO
    // add filter on financial type id

    switch ($code) {
      case 'NRG New':
        break;
      case 'NRG 001':
        break;
      case 'NRG 010';
        break;
      case 'NRG 011';
        break;
      case 'NRG 100';
        break;
      case 'NRG 101';
        break;
      case 'NRG 110';
        break;
      case 'NRG 111';
        break;
    }

    return $where;
  }

  public function getGroupBy() {
    return ' contact_a.id';
  }
}
