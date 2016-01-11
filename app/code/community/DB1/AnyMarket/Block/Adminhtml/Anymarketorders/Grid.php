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
 * Anymarket Orders admin grid block
 *
 * @category    DB1
 * @package     DB1_AnyMarket

 */
class DB1_AnyMarket_Block_Adminhtml_Anymarketorders_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * constructor
     *
     * @access public

     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('anymarketordersGrid');
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
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketorders_Grid

     */
    protected function _prepareCollection()
    {
        $store_id = $this->_getStore();
        Mage::app()->setCurrentStore($store_id);
        $store_id = Mage::app()->getStore()->getId();
        Mage::getSingleton('core/session')->setStoreListOrderVariable($store_id);
        $collection = Mage::getModel('db1_anymarket/anymarketorders')
            ->getCollection();
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare grid collection
     *
     * @access protected
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketorders_Grid

     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'nmo_id_order',
            array(
                'header'    => Mage::helper('db1_anymarket')->__('Code Order Magento'),
                'align'     => 'left',
                'index'     => 'nmo_id_order',
            )
        );
        $this->addColumn(
            'nmo_id_anymarket',
            array(
                'header' => Mage::helper('db1_anymarket')->__('Code Anymarket'),
                'index'  => 'nmo_id_anymarket',
                'type'=> 'text',

            )
        );
        $this->addColumn(
            'nmo_status_int',
            array(
                'header' => Mage::helper('db1_anymarket')->__('Integration status'),
                'index'  => 'nmo_status_int',
                'type'=> 'text',

            )
        );
        $this->addColumn(
            'nmo_desc_error',
            array(
                'header' => Mage::helper('db1_anymarket')->__('Error Message'),
                'index'  => 'nmo_desc_error',
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
        $this->addExportType('*/*/exportCsv', Mage::helper('db1_anymarket')->__('CSV'));
        $this->addExportType('*/*/exportExcel', Mage::helper('db1_anymarket')->__('Excel'));
        $this->addExportType('*/*/exportXml', Mage::helper('db1_anymarket')->__('XML'));
        return parent::_prepareColumns();
    }

    /**
     * prepare mass action
     *
     * @access protected
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketorders_Grid

     */
    protected function _prepareMassaction()
    {

        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('anymarketorders');

        $this->getMassactionBlock()->addItem(
            'sincronizar',
            array(
                'label'=> Mage::helper('db1_anymarket')->__('Synchronize'),
                'url'  => $this->getUrl('*/*/massSincOrder'),
                'confirm'  => Mage::helper('db1_anymarket')->__('Are you sure you want to sync?')
            )
        );

        return $this;
    }

    /**
     * get the row url
     *
     * @access public
     * @param DB1_AnyMarket_Model_Anymarketorders
     * @return string

     */
    public function getRowUrl($row)
    {
        $_pullOrder = Mage::getModel('sales/order')->loadByIncrementId( $row->getNmoIdOrder() );
        if($_pullOrder != null){
            return Mage::helper('adminhtml')->getUrl('adminhtml/sales_order/view', array('order_id' => $_pullOrder->getId())); 
        }else{
            return null;
        }
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
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketorders_Grid

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
     * @param DB1_AnyMarket_Model_Resource_Anymarketorders_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketorders_Grid

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
