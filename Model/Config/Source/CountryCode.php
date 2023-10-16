<?php

namespace Abhinay\Messente\Model\Config\Source;

/**
 * Class CountryCode
 * @package Abhinay\Messente\Model\Config\Source
 */
class CountryCode extends \Magento\Config\Model\Config\Backend\Serialized\ArraySerialized
{
    /**
     * @return string
     */
    protected function _afterLoad()
    {
        $countryCode = $this->getValue();
        if (!empty($countryCode)) {
            $arrayCountryCode = json_decode($countryCode, true ?? '');
            if ((!empty($arrayCountryCode)) && (!is_array($arrayCountryCode))) return '';
            foreach ($arrayCountryCode as $key => $value) {
                if ((!empty($arrayCountryCode)) && (!is_array($value))) {
                    unset($arrayCountryCode[$key]);
                    continue;
                }
            }
            $this->setValue($arrayCountryCode);
        } else {
            return '';
        }
    }
}
