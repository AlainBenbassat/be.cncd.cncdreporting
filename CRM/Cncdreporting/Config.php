<?php

class CRM_Cncdreporting_Config {
  public function getOptionGroup_Ambassadors() {
    $params = [
      'name' => 'cncd_ambassadeurs',
      'title' => 'Ambassadeurs CNCD',
      'data_type' => 'String',
      'is_reserved' => '0',
      'is_active' => '1',
      'is_locked' => '0'
    ];
    $options = ['David' ,'Fabiola' ,'Fabrizio' ,'Jacques' ,'Marie' ,'Thérèse', 'Tsige-Hana' ,'Vanessa' ,'Zoe'];
    return $this->createOrGetOptionGroup($params, $options, '');
  }

  private function createOrGetOptionGroup($params, $options, $defaultOption = '') {
    $recreateOptions = FALSE;

    try {
      $optionGroup = civicrm_api3('OptionGroup', 'getsingle', [
        'name' => $params['name'],
      ]);
    }
    catch (Exception $e) {
      $optionGroup = civicrm_api3('OptionGroup', 'create', $params);
      $recreateOptions = TRUE;
    }

    if ($recreateOptions) {
      // delete existing options
      $sql = "delete from civicrm_option_value where option_group_id = " . $optionGroup['id'];
      CRM_Core_DAO::executeQuery($sql);

      // add the options
      $i = 1;
      foreach ($options as $option) {
        civicrm_api3('OptionValue', 'create', [
          'option_group_id' => $optionGroup['id'],
          'label' => $option,
          'value' => $i,
          'name' => CRM_Utils_String::munge($option, '_', 64),
          'is_default' => ($option == $defaultOption) ? 1 : '0',
          'weight' => $i,
          'is_optgroup' => '0',
          'is_reserved' => '0',
          'is_active' => '1'
        ]);
        $i++;
      }
    }

    return $optionGroup;
  }

}
