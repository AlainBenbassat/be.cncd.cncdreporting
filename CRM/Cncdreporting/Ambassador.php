<?php

class CRM_Cncdreporting_Ambassador {
  public function getStatSepaStreet($ambassadorName, $fromDate, $toDate) {
    [$sourceOperator, $sourceValue] = $this->getSourceOperatorAndValue($ambassadorName);

    $sql = "
      select
        count(*)
      from
        civicrm_sdd_mandate m
      inner join
        civicrm_contact c on c.id = m.contact_id
      where
        m.type = 'RCUR'
      and
        m.source $sourceOperator %1
      and
        m.date between %2 and %3
      and
        c.is_deleted = 0
    ";
    $sqlParams = [
      1 => [$sourceValue, 'String'],
      2 => [$fromDate, 'String'],
      3 => [$toDate, 'String'],
    ];

    return CRM_Core_DAO::singleValueQuery($sql, $sqlParams);
  }

  public function getStatSepaReal($ambassadorName, $fromDate, $toDate) {
    [$sourceOperator, $sourceValue] = $this->getSourceOperatorAndValue($ambassadorName);

    $sql = "
      select
        count(*)
      from
        civicrm_sdd_mandate m
      inner join
        civicrm_contact c on c.id = m.contact_id
      where
        m.type = 'RCUR'
      and
        m.source $sourceOperator %1
      and
        m.date between %2 and %3
      and
        m.status <> 'COMPLETE'
      and
        c.is_deleted = 0
    ";
    $sqlParams = [
      1 => [$sourceValue, 'String'],
      2 => [$fromDate, 'String'],
      3 => [$toDate, 'String'],
    ];

    return CRM_Core_DAO::singleValueQuery($sql, $sqlParams);
  }

  public function getStatSepaRealWithValue($ambassadorName, $fromDate, $toDate, $valueExpression) {
    [$sourceOperator, $sourceValue] = $this->getSourceOperatorAndValue($ambassadorName);

    $sql = "
      select
        count(*)
      from
        civicrm_sdd_mandate m
      inner join
        civicrm_contact c on c.id = m.contact_id
      inner join
        civicrm_contribution_recur cbr on cbr.id = m.entity_id
      where
        m.type = 'RCUR'
      and
        m.source $sourceOperator %1
      and
        m.date between %2 and %3
      and
        m.status <> 'COMPLETE'
      and
        cbr.amount $valueExpression
      and
        c.is_deleted = 0
    ";
    $sqlParams = [
      1 => [$sourceValue, 'String'],
      2 => [$fromDate, 'String'],
      3 => [$toDate, 'String'],
    ];

    return CRM_Core_DAO::singleValueQuery($sql, $sqlParams);
  }

  public function getStatSepaCompleted($ambassadorName, $fromDate, $toDate) {
    [$sourceOperator, $sourceValue] = $this->getSourceOperatorAndValue($ambassadorName);

    $sql = "
      select
        count(*)
      from
        civicrm_sdd_mandate m
      inner join
        civicrm_contact c on c.id = m.contact_id
      where
        m.type = 'RCUR'
      and
        m.source $sourceOperator %1
      and
        m.date between %2 and %3
      and
        m.status = 'COMPLETE'
      and
        c.is_deleted = 0
    ";
    $sqlParams = [
      1 => [$sourceValue, 'String'],
      2 => [$fromDate, 'String'],
      3 => [$toDate, 'String'],
    ];

    return CRM_Core_DAO::singleValueQuery($sql, $sqlParams);
  }

  public function getStatSepaCompletedBeforeFirst($ambassadorName, $fromDate, $toDate) {
    [$sourceOperator, $sourceValue] = $this->getSourceOperatorAndValue($ambassadorName);

    $sql = "
      select
        count(*)
      from
        civicrm_sdd_mandate m
      inner join
        civicrm_contact c on c.id = m.contact_id
      left outer join
        civicrm_contribution cb on cb.id = m.first_contribution_id
      where
        m.type = 'RCUR'
      and
        m.source $sourceOperator %1
      and
        m.date between %2 and %3
      and
        m.status = 'COMPLETE'
      and
        (cb.id is null or cb.contribution_status_id > 1)
      and
        c.is_deleted = 0
    ";
    $sqlParams = [
      1 => [$sourceValue, 'String'],
      2 => [$fromDate, 'String'],
      3 => [$toDate, 'String'],
    ];

    return CRM_Core_DAO::singleValueQuery($sql, $sqlParams);
  }

  public function getStatSepaCancelledFirst($ambassadorName, $fromDate, $toDate) {
    [$sourceOperator, $sourceValue] = $this->getSourceOperatorAndValue($ambassadorName);

    $sql = "
      select
        count(*)
      from
        civicrm_sdd_mandate m
      inner join
        civicrm_contact c on c.id = m.contact_id
      left outer join
        civicrm_contribution cb on cb.id = m.first_contribution_id
      where
        m.type = 'RCUR'
      and
        m.source $sourceOperator %1
      and
        m.date between %2 and %3
      and
        cb.contribution_status_id = 3
      and
        c.is_deleted = 0
    ";
    $sqlParams = [
      1 => [$sourceValue, 'String'],
      2 => [$fromDate, 'String'],
      3 => [$toDate, 'String'],
    ];

    return CRM_Core_DAO::singleValueQuery($sql, $sqlParams);
  }

  public function getStatSepaAverageAge($ambassadorName, $fromDate, $toDate) {
    [$sourceOperator, $sourceValue] = $this->getSourceOperatorAndValue($ambassadorName);

    $sql = "
      select
        floor(avg(TIMESTAMPDIFF(YEAR, c.birth_date, CURDATE())))
      from
        civicrm_sdd_mandate m
      inner join
        civicrm_contact c on c.id = m.contact_id
      where
        m.type = 'RCUR'
      and
        m.source $sourceOperator %1
      and
        m.date between %2 and %3
      and
        m.status <> 'COMPLETE'
      and
        c.is_deleted = 0
    ";
    $sqlParams = [
      1 => [$sourceValue, 'String'],
      2 => [$fromDate, 'String'],
      3 => [$toDate, 'String'],
    ];

    return CRM_Core_DAO::singleValueQuery($sql, $sqlParams);
  }

  public function getStatSepaRealUnderAge25($ambassadorName, $fromDate, $toDate) {
    [$sourceOperator, $sourceValue] = $this->getSourceOperatorAndValue($ambassadorName);

    $sql = "
      select
        count(*)
      from
        civicrm_sdd_mandate m
      inner join
        civicrm_contact c on c.id = m.contact_id
      where
        m.type = 'RCUR'
      and
        m.source $sourceOperator %1
      and
        m.date between %2 and %3
      and
        m.status <> 'COMPLETE'
      and
        c.is_deleted = 0
      and
        TIMESTAMPDIFF(YEAR, c.birth_date, CURDATE()) < 25
    ";
    $sqlParams = [
      1 => [$sourceValue, 'String'],
      2 => [$fromDate, 'String'],
      3 => [$toDate, 'String'],
    ];

    return CRM_Core_DAO::singleValueQuery($sql, $sqlParams);
  }

  public function getAllAmbassadors() {
    $ambassadors = [];

    $config = new CRM_Cncdreporting_Config();
    $ambassadorGroupId = $config->getOptionGroup_Ambassadors()['id'];

    $sql = "
      select
        label
      from
        civicrm_option_value
      where
        option_group_id = $ambassadorGroupId
      and
        is_active = 1
      order by
        weight
    ";
    $dao = CRM_Core_DAO::executeQuery($sql);
    while ($dao->fetch()) {
      $ambassadors[] = $dao->label;
    }

    return $ambassadors;
  }

  private function getSourceOperatorAndValue($ambassadorName) {
    if (empty($ambassadorName)) {
      $operator = 'not like';
      $value = 'mailing%';
    }
    else {
      $operator = 'like';
      $value = "%$ambassadorName%";
    }

    return [$operator, $value];
  }
}
