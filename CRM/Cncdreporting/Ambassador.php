<?php

class CRM_Cncdreporting_Ambassador {
  public function getStatSepaStreet($ambassadorName, $fromDate, $toDate) {
    $sql = "
      select
        count(*)
      from
        civicrm_sdd_mandate m
      inner join
        civicrm_contact c on c.id = m.contact_id
      where
        m.source like %1
      and
        m.date between %2 and %3
      and
        c.is_deleted = 0
    ";
    $sqlParams = [
      1 => ['%' . $ambassadorName . '%', 'String'],
      2 => [$fromDate, 'String'],
      3 => [$toDate, 'String'],
    ];

    return CRM_Core_DAO::singleValueQuery($sql, $sqlParams);
  }

  public function getStatSepaReal($ambassadorName, $fromDate, $toDate) {
    $sql = "
      select
        count(*)
      from
        civicrm_sdd_mandate m
      inner join
        civicrm_contact c on c.id = m.contact_id
      where
        m.source like %1
      and
        m.date between %2 and %3
      and
        m.status <> 'COMPLETE'
      and
        c.is_deleted = 0
    ";
    $sqlParams = [
      1 => ['%' . $ambassadorName . '%', 'String'],
      2 => [$fromDate, 'String'],
      3 => [$toDate, 'String'],
    ];

    return CRM_Core_DAO::singleValueQuery($sql, $sqlParams);
  }

  public function getStatSepaAverageAge($ambassadorName, $fromDate, $toDate) {
    $sql = "
      select
        floor(avg(TIMESTAMPDIFF(YEAR, c.birth_date, CURDATE())))
      from
        civicrm_sdd_mandate m
      inner join
        civicrm_contact c on c.id = m.contact_id
      where
        m.source like %1
      and
        m.date between %2 and %3
      and
        m.status <> 'COMPLETE'
      and
        c.is_deleted = 0
      group by
        c.id
    ";
    $sqlParams = [
      1 => ['%' . $ambassadorName . '%', 'String'],
      2 => [$fromDate, 'String'],
      3 => [$toDate, 'String'],
    ];

    return CRM_Core_DAO::singleValueQuery($sql, $sqlParams);
  }

  public function getStatSepaRealMinus25($ambassadorName, $fromDate, $toDate) {
    $sql = "
      select
        count(*)
      from
        civicrm_sdd_mandate m
      inner join
        civicrm_contact c on c.id = m.contact_id
      where
        m.source like %1
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
      1 => ['%' . $ambassadorName . '%', 'String'],
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
}
