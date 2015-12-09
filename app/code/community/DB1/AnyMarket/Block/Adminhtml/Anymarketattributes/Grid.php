<?php
/**
 * DB1_AnyMarket extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       DB1
 * @package        DB1_AnyMarket
 * @copyright      Copyright (c) 2015
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Anymarket Attributes admin grid block
 *
 * @category    DB1
 * @package     DB1_AnyMarket
 */
class DB1_AnyMarket_Block_Adminhtml_Anymarketattributes_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * constructor
     *
     * @access public     
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('anymarketattributesGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * prepare collection
     *
     * @access protected
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketattributes_Grid
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('db1_anymarket/anymarketattributes')
            ->getCollection();
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare grid collection
     *
     * @access protected
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketattributes_Grid    
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'nma_desc',
            array(
                'header'    => Mage::helper('db1_anymarket')->__('Descrição do Atributo'),
                'align'     => 'left',
                'index'     => 'nma_desc',
            )
        );
        
        $this->addColumn(
            'status',
            array(
                'header'  => Mage::helper('db1_anymarket')->__('Status'),
                'index'   => 'status',
                'type'    => 'options',
                'options' => array(
                    '1' => Mage::helper('db1_anymarket')->__('Enabled'),
                    '0' => Mage::helper('db1_anymarket')->__('Disabled'),
                )
            )
        );
        $this->addExportType('*/*/exportCsv', Mage::helper('db1_anymarket')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('db1_anymarket')->__('Excel'));
        $this->addExportType('*/*/exportXml', Mage::helper('db1_anymarket')->__('XML'));
        return parent::_prepareColumns();
    }

    /**
     * prepare mass action
     *
     * @access protected
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketattributes_Grid  
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('anymarketattributes');
        $this->getMassactionBlock()->addItem(
            'status',
            array(
                'label'      => Mage::helper('db1_anymarket')->__('Change status'),
                'url'        => $this->getUrl('*/*/massStatus', array('_current'=>true)),
                'additional' => array(
                    'status' => array(
                        'name'   => 'status',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => Mage::helper('db1_anymarket')->__('Status'),
                        'values' => array(
                            '1' => Mage::helper('db1_anymarket')->__('Enabled'),
                            '0' => Mage::helper('db1_anymarket')->__('Disabled'),
                        )
                    )
                )
            )
        );
        return $this;
    }

    /**
     * get the row url
     *
     * @access public
     * @param DB1_AnyMarket_Model_Anymarketattributes
     * @return string
     */
    public function getRowUrl($row)
    {
        //return $this->getUrl('*/*/edit', array('id' => $row->getId()));
        return Mage::helper('adminhtml')->getUrl('adminhtml/catalog_product_attribute/edit', array('attribute_id' => $row->getNmaIdAttr() ));
    }

    /**
     * get the grid url
     *
     * @access public
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    /**
     * after collection load
     *
     * @access protected
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketattributes_Grid
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    /**
     * filter store column
     *
     * @access protected
     * @param DB1_AnyMarket_Model_Resource_Anymarketattributes_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketattributes_Grid
     */
    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $collection->addStoreFilter($value);
        return $this;
    }
}
