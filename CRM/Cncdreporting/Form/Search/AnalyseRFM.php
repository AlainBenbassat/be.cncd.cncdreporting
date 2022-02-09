<?php
use CRM_Cncdreporting_ExtensionUtil as E;

class CRM_Cncdreporting_Form_Search_AnalyseRFM extends CRM_Contact_Form_Search_Custom_Base implements CRM_Contact_Form_Search_Interface {
  private $rfm;

  public function __construct(&$formValues) {
    $this->rfm = new CRM_Cncdreporting_RFM();

    parent::__construct($formValues);
  }

  public function buildForm(&$form) {
    CRM_Utils_System::setTitle('Analyse RFM');

    $rfmFormFilters = $this->getRfmFormFilters($form);

    $form->assign('elements', $rfmFormFilters);
  }

  public function summary() {
    return [
      'summary' => 'This is a summary',
      'total' => '',
    ];
  }

  public function &columns() {
    $columns = [
      E::ts('Contact Id') => 'contact_id',
      E::ts('Name') => 'sort_name',
      E::ts('Email') => 'email',
      E::ts('Postal Code') => 'postal_code',
      E::ts('City') => 'city',
    ];
    return $columns;
  }

  function all($offset = 0, $rowcount = 0, $sort = NULL, $includeContactIDs = FALSE, $justIDs = FALSE) {
    $sql = $this->sql($this->select(), $offset, $rowcount, $sort, $includeContactIDs, $this->groupBy());
    //die($sql);
    return $sql;
  }

  public function select() {
    return $this->rfm->getSelect();
  }

  public function from() {
    return ' FROM ' . $this->rfm->getFrom();
  }

  public function where($includeContactIDs = FALSE) {
    $referenceYear = CRM_Utils_Array::value('reference_year', $this->_formValues);
    $categoryCode = CRM_Utils_Array::value('category_code', $this->_formValues);
    $averageAmountFrom = CRM_Utils_Array::value('average_amount_from', $this->_formValues);
    $averageAmountTo = CRM_Utils_Array::value('average_amount_to', $this->_formValues);
    $ageFrom = CRM_Utils_Array::value('age_from', $this->_formValues);
    $ageTo = CRM_Utils_Array::value('age_to', $this->_formValues);

    $params = [];
    $where = $this->rfm->getWhere($referenceYear, $categoryCode);

    return $this->whereClause($where, $params);
  }

  public function groupBy() {
    return ' GROUP BY' . $this->rfm->getGroupBy();
  }

  private function getRfmFormFilters(&$form) {
    $filters = [];

    $filters[] = $this->getRfmFormFilterYears($form);

    $filters[] = $this->getRfmFormFilterCodes($form);

    $filters[] = $this->getRfmFormFilterAverageAmountFrom($form);
    $filters[] = $this->getRfmFormFilterAverageAmountTo($form);

    $filters[] = $this->getRfmFormFilterAgeFrom($form);
    $filters[] = $this->getRfmFormFilterAgeTo($form);

    return $filters;
  }

  private function getRfmFormFilterYears(&$form) {
    $elementName = 'reference_year';

    $years = [];

    $y = date('Y');
    for ($i = 0; $i < 5; $i++) {
      $years[$y - $i] = $y - $i;
    }

    $form->addElement('select', $elementName, 'Année de référence', $years);

    return $elementName;
  }

  private function getRfmFormFilterCodes(&$form) {
    $elementName = 'category_code';

    $codes = $this->rfm->getCategoryCodes();

    $form->addElement('select', $elementName, 'Code RFM', $codes);

    return $elementName;
  }

  private function getRfmFormFilterAverageAmountFrom(&$form) {
    $elementName = 'average_amount_from';

    $form->addMoney($elementName, 'Valeur moyenne de', FALSE, NULL, FALSE);

    return $elementName;
  }

  private function getRfmFormFilterAverageAmountTo(&$form) {
    $elementName = 'average_amount_to';

    $form->addMoney($elementName, 'à', FALSE, NULL, FALSE);

    return $elementName;
  }

  private function getRfmFormFilterAgeFrom(&$form) {
    $elementName = 'age_from';

    $form->add('text', $elementName, 'Age de');

    return $elementName;
  }

  private function getRfmFormFilterAgeTo(&$form) {
    $elementName = 'age_to';

    $form->add('text', $elementName, 'à');

    return $elementName;
  }

  public function templateFile() {
    return 'CRM/Contact/Form/Search/Custom.tpl';
  }

}
