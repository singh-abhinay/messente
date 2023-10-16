<?php

namespace Abhinay\Messente\Model\Config\Source;

/**
 * Class CountryList
 * @package Abhinay\Messente\Model\Config\Source
 */
class CountryList implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\Collection
     */
    protected $_directory;

    /**
     * CountryList constructor.
     * @param \Magento\Directory\Model\ResourceModel\Country\Collection $directory
     */
    public function __construct(
        \Magento\Directory\Model\ResourceModel\Country\Collection $directory
    )
    {
        $this->_directory = $directory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $arrayCountryCode = [];
        $options = $this->_directory->toOptionArray('Country');
        foreach ($options as $key=> $code){
            if ((array_key_exists('value',$code)) && (!empty($code['value']))){
                $arrayCountryCode[$key]['value'] = $code['value'];
                $arrayCountryCode[$key]['label'] = $code['label'];
            }
        }
        return $arrayCountryCode;
    }
}