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
 * Anymarket Categories admin grid block
 *
 * @category    DB1
 * @package     DB1_AnyMarket

 */
class DB1_AnyMarket_Block_Adminhtml_Anymarketcategories_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * constructor
     *
     * @access public
     
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('anymarketcategoriesGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * get current store scope
     *
     * @access protected
     * @return store view
     
     */
    protected function _getStore()
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    /**
     * prepare collection
     *
     * @access protected
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketcategories_Grid
     
     */
    protected function _prepareCollection()
    {
        $store_id = $this->_getStore();
        Mage::app()->setCurrentStore($store_id);
        $store_id = Mage::app()->getStore()->getId();
        Mage::getSingleton('core/session')->setStoreCategVariable($store_id);
        $collection = Mage::getModel('db1_anymarket/anymarketcategories')
            ->getCollection();
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare grid collection
     *
     * @access protected
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketcategories_Grid
     
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            array(
                'header' => Mage::helper('db1_anymarket')->__('Id'),
                'index'  => 'entity_id',
                'type'   => 'number'
            )
        );
        $this->addColumn(
            'nmc_cat_desc',
            array(
                'header'    => Mage::helper('db1_anymarket')->__('Category description'),
                'align'     => 'left',
                'index'     => 'nmc_cat_desc',
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
        $this->addColumn(
            'nmc_cat_id',
            array(
                'header' => Mage::helper('db1_anymarket')->__('Complete code Category'),
                'index'  => 'nmc_cat_id',
                'type'=> 'text',

            )
        );
        $this->addColumn(
            'nmc_cat_root_id',
            array(
                'header' => Mage::helper('db1_anymarket')->__('Code of predecessor category'),
                'index'  => 'nmc_cat_root_id',
                'type'=> 'text',

            )
        );
        if (!Mage::app()->isSingleStoreMode() && !$this->_isExport) {
            $this->addColumn(
                'store_id',
                array(
                    'header'     => Mage::helper('db1_anymarket')->__('Store Views'),
                    'index'      => 'store_id',
                    'type'       => 'store',
                    'store_all'  => true,
                    'store_view' => true,
                    'sortable'   => false,
                    'filter_condition_callback'=> array($this, '_filterStoreCondition'),
                )
            );
        }
        $this->addColumn(
            'created_at',
            array(
                'header' => Mage::helper('db1_anymarket')->__('Created at'),
                'index'  => 'created_at',
                'width'  => '120px',
                'type'   => 'datetime',
            )
        );
        $this->addColumn(
            'updated_at',
            array(
                'header'    => Mage::helper('db1_anymarket')->__('Updated at'),
                'index'     => 'updated_at',
                'width'     => '120px',
                'type'      => 'datetime',
            )
        );
        $this->addColumn(
            'action',
            array(
                'header'  =>  Mage::helper('db1_anymarket')->__('Action'),
                'width'   => '100',
                'type'    => 'action',
                'getter'  => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('db1_anymarket')->__('Edit'),
                        'url'     => array('base'=> '*/*/edit'),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'is_system' => true,
                'sortable'  => false,
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
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketcategories_Grid
     
     */
    protected function _prepareMassaction()
    {

        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('anymarketcategories');
        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label'=> Mage::helper('db1_anymarket')->__('Delete'),
                'url'  => $this->getUrl('*/*/massDelete'),
                'confirm'  => Mage::helper('db1_anymarket')->__('Are you sure?')
            )
        );
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
     * @param DB1_AnyMarket_Model_Anymarketcategories
     * @return string
     
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
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
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketcategories_Grid
     
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
     * @param DB1_AnyMarket_Model_Resource_Anymarketcategories_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketcategories_Grid
     
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
