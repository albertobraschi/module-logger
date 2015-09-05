<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @method \Magento\Config\Block\System\Config\Form getForm()
 */
namespace WebShopApps\Logger\Block\Config\Form\Fieldset\Modules;

class DisableLogging extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var \Magento\Framework\Object
     */
    protected $_dummyElement;

    /**
     * @var \Magento\Config\Block\System\Config\Form\Field
     */
    protected $_fieldRenderer;

    /**
     * @var array
     */
    protected $_values;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $_moduleList;


    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->scopeConfig = $scopeConfig;
        $this->_moduleList = $moduleList;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $this->_getHeaderHtml($element);

        $modules = $this->_moduleList->getNames();

        $wsaApproved = array('webshopapps','shipperhq');

        sort($modules);

        $viewAllExtns = $this->_scopeConfig->isSetFlag(
            'wsalogmenu/wsalogger/view_all_extns',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        foreach ($modules as $moduleName) {
            if ($moduleName==='Mage_Adminhtml' ||$moduleName==='WebShopApps_Common' || $moduleName==='WebShopApps_Logger'
                || stripos($moduleName,'Mage_') !== false) {
                continue;
            }

            $providerArray = explode('_',$moduleName);
            $provider = strtolower($providerArray[0]);

            if (!$viewAllExtns && !in_array($provider, $wsaApproved)){
                continue;
            }
            $html.= $this->_getFieldHtml($element, $moduleName);
        }
        $html .= $this->_getFooterHtml($element);

        return $html;
    }


    /**
     * @return \Magento\Framework\DataObject
     */
    protected function _getDummyElement()
    {
        if (empty($this->_dummyElement)) {
            $this->_dummyElement = new \Magento\Framework\DataObject(['showInDefault' => 1, 'showInWebsite' => 1]);
        }
        return $this->_dummyElement;
    }


    /**
     * @return \Magento\Config\Block\System\Config\Form\Field
     */
    protected function _getFieldRenderer()
    {
        if (empty($this->_fieldRenderer)) {
            $this->_fieldRenderer = $this->_layout->getBlockSingleton(
                'Magento\Config\Block\System\Config\Form\Field'
            );
        }
        return $this->_fieldRenderer;
    }

    /**
     * @return array
     */
    protected function _getValues()
    {
        if (empty($this->_values)) {
            $this->_values = [
                ['label' => __('Logging Enabled'), 'value' => 0],
                ['label' => __('Logging Disabled'), 'value' => 1],
            ];
        }
        return $this->_values;
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param string $moduleName
     * @return mixed
     */
    protected function _getFieldHtml($fieldset, $moduleName)
    {
        $configData = $this->getConfigData();
        $path = 'wsalogmenu/wsa_module_log/' . $moduleName;
        //TODO: move as property of form
        if (isset($configData[$path])) {
            $data = $configData[$path];
            $inherit = false;
        } else {
            $data = (int)(string)$this->getForm()->getConfigValue($path);
            $inherit = true;
        }

        $element = $this->_getDummyElement();

        $field = $fieldset->addField(
            $moduleName,
            'select',
            [
                'name' => 'groups[wsa_module_log][fields][' . $moduleName . '][value]',
                'label' => $moduleName,
                'value' => $data,
                'values' => $this->_getValues(),
                'inherit' => $inherit,
                'can_use_default_value' => $this->getForm()->canUseDefaultValue($element),
                'can_use_website_value' => $this->getForm()->canUseWebsiteValue($element)
            ]
        )->setRenderer(
            $this->_getFieldRenderer()
        );

        return $field->toHtml();
    }
}