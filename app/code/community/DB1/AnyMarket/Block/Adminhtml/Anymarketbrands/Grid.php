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
 * @copyright      Copyright (c) 2016
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Anymarketbrands admin grid block
 *
 * @category    DB1
 * @package     DB1_AnyMarket

 */
class DB1_AnyMarket_Block_Adminhtml_Anymarketbrands_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * constructor
     *
     * @access public
     
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('anymarketbrandsGrid');
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
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketbrands_Grid
     
     */
    protected function _prepareCollection()
    {
        $store_id = $this->_getStore();
        Mage::app()->setCurrentStore($store_id);
        $store_id = Mage::helper('db1_anymarket')->getCurrentStoreView();
        Mage::getSingleton('core/session')->setStoreBrandVariable($store_id);
        $collection = Mage::getModel('db1_anymarket/anymarketbrands')
            ->getCollection();
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare grid collection
     *
     * @access protected
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketbrands_Grid
     
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'brd_id',
            array(
                'header'    => Mage::helper('db1_anymarket')->__('Brand Code'),
                'align'     => 'left',
                'index'     => 'brd_id',
                'type'   => 'number'
            )
        );
        $this->addColumn(
            'brd_name',
            array(
                'header' => Mage::helper('db1_anymarket')->__('Brand Description'),
                'index'  => 'brd_name',
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
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketbrands_Grid
     
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('anymarketbrands');
        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label'=> Mage::helper('db1_anymarket')->__('Delete'),
                'url'  => $this->getUrl('*/*/massDelete'),
                'confirm'  => Mage::helper('db1_anymarket')->__('Are you sure?')
            )
        );
        return $this;
    }

    /**
     * get the row url
     *
     * @access public
     * @param DB1_AnyMarket_Model_Anymarketbrands
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
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketbrands_Grid
     
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
     * @param DB1_AnyMarket_Model_Resource_Anymarketbrands_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketbrands_Grid
     
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
