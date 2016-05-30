<?php

class DB1_AnyMarket_Block_Adminhtml_System_Config_Form_Callback
    extends Mage_Adminhtml_Block_Abstract implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Render element html
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $useContainerId = $element->getData('use_container_id');
        return sprintf('<tr id="row_%s">
                            <td class="label">
                                <h4 id="%s">%s</h4>
                            </td>
                            <td class="label">%s</td>
                       </tr>',
            $element->getHtmlId(), $element->getHtmlId(), $element->getLabel(), Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB)."index.php/anymarketcallback/index/sinc"
        );
    }
}
