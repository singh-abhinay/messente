<?php

namespace Abhinay\Messente\Block\Adminhtml\Customer;

/**
 * Class CountryCode
 * @package Abhinay\Messente\Block\Adminhtml\Customer
 */
class CountryCode extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var \Abhinay\Messente\Model\Config\Source\CountryList
     */
    protected $countrylist;
    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $elementFactory;

    /**
     * CountryCode constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory
     * @param \Abhinay\Messente\Model\Config\Source\CountryList $countrylist
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        \Abhinay\Messente\Model\Config\Source\CountryList $countrylist,
        array $data = []
    )
    {
        $this->countrylist = $countrylist;
        $this->elementFactory = $elementFactory;
        $this->addColumn('code', ['label' => __('Country Code')]);
        $this->addColumn('country_id', ['label' => __('Country Name')]);
        $this->_addButtonLabel = __('Add New Code');
        $this->_addAfter = false;
        parent::__construct($context, $data);
    }

    /**
     * @param string $country
     * @return mixed|string
     */
    public function renderCellTemplate($country)
    {
        $countrylist = $this->countrylist->toOptionArray();
        if (($country == 'country_id') && (isset($this->_columns[$country]))) {
            $formHtml = $this->getHtmlStructure($country, $countrylist);
            return str_replace("\n", '', $formHtml->getElementHtml());
        }
        return parent::renderCellTemplate($country);
    }

    /**
     * @param $columns
     * @param $countrylist
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function getHtmlStructure($columns, $countrylist)
    {
        $htmlElement = $this->elementFactory->create('select');
        $htmlElement->setForm(
            $this->getForm()
        )->setName(
            $this->_getCellInputElementName($columns)
        )->setHtmlId(
            $this->_getCellInputElementId('<%- _id %>', $columns)
        )->setValues(
            $countrylist
        );
        return $htmlElement;
    }
}
