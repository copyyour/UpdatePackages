<?php
/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * *********************************************************************************** */

class Vtiger_Currency_UIType extends Vtiger_Base_UIType
{

	protected $edit = false;

	/**
	 * Function to get the Template name for the current UI Type object
	 * @return <String> - Template Name
	 */
	public function getTemplateName()
	{
		return 'uitypes/Currency.tpl';
	}

	/**
	 * Function to get the Display Value, for the current field type with given DB Insert Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDisplayValue($value, $recordId = false)
	{
		global $default_charset;
		$uiType = $this->get('field')->get('uitype');
		if ($value) {
			if ($uiType == 72) {
				// Some of the currency fields like Unit Price, Totoal , Sub-total - doesn't need currency conversion during save
				$value = CurrencyField::convertToUserFormat($value, null, true);
			} else {
				$value = CurrencyField::convertToUserFormat($value);
			}
			if (!$this->edit) {
				$value = $this->getDetailViewDisplayValue($value, $recordId, $uiType);
			}
			return $value;
		}
		return null;
	}

	/**
	 * Function to get the Value of the field in the format, the user provides it on Save
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getUserRequestValue($value, $recordId)
	{
		return $this->getDisplayValue($value, $recordId);
	}

	/**
	 * Function to get the DB Insert Value, for the current field type with given User Value
	 * @param <Object> $value
	 * @return <Object>
	 */
	public function getDBInsertValue($value)
	{
		$uiType = $this->get('field')->get('uitype');
		if ($uiType == 72) {
			return self::convertToDBFormat($value, null, true);
		} else {
			return self::convertToDBFormat($value);
		}
	}

	/**
	 * Function to transform display value for currency field
	 * @param $value
	 * @param Current User
	 * @param <Boolean> Skip Conversion
	 * @return converted user format value
	 */
	public static function transformDisplayValue($value, $user = null, $skipConversion = false)
	{
		return CurrencyField::convertToUserFormat($value, $user, $skipConversion);
	}

	/**
	 * Function converts User currency format to database format
	 * @param <Object> $value - Currency value
	 * @param <User Object> $user
	 * @param <Boolean> $skipConversion
	 */
	public static function convertToDBFormat($value, $user = null, $skipConversion = false)
	{
		return CurrencyField::convertToDBFormat($value, $user, $skipConversion);
	}

	/**
	 * Function to get the display value in edit view
	 * @param <String> $value
	 * @return <String>
	 */
	public function getEditViewDisplayValue($value)
	{
		if (!empty($value)) {
			$this->edit = true;
			return $this->getDisplayValue($value);
		}
		return $value;
	}

	/**
	 * Function that converts the Number into Users Currency along with currency symbol
	 * @param Users $user
	 * @param Boolean $skipConversion
	 * @return Formatted Currency
	 */
	public function getDetailViewDisplayValue($value, $recordId, $uiType)
	{
		$currencyModal = new CurrencyField($value);
		$currencyModal->initialize();

		if ($uiType == '72' && $recordId) {
			$moduleName = $this->get('field')->getModuleName();
			if ($this->get('field')->getName() == 'unit_price') {
				$currencyId = getProductBaseCurrency($recordId, $moduleName);
				$cursym_convrate = getCurrencySymbolandCRate($currencyId);
				$currencySymbol = $cursym_convrate['symbol'];
			} else {
				$currencyInfo = getInventoryCurrencyInfo($moduleName, $recordId);
				$currencySymbol = $currencyInfo['currency_symbol'];
			}
		} else {
			$currencySymbol = $currencyModal->currencySymbol;
		}
		return $currencyModal->appendCurrencySymbol($value, $currencySymbol);
	}
}
