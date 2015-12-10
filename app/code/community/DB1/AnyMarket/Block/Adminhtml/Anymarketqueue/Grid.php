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
 * Anymarket Queue admin grid block
 *
 * @category    DB1
 * @package     DB1_AnyMarket
 */
class DB1_AnyMarket_Block_Adminhtml_Anymarketqueue_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * constructor
     *
     * @access public
     
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('anymarketqueueGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * prepare collection
     *
     * @access protected
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketqueue_Grid
     
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('db1_anymarket/anymarketqueue')
            ->getCollection();
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare grid collection
     *
     * @access protected
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketqueue_Grid
     
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
            'nmq_id',
            array(
                'header'    => Mage::helper('db1_anymarket')->__('Item code that is waiting in the queue'),
                'align'     => 'left',
                'index'     => 'nmq_id',
            )
        );
        $this->addColumn(
            'nmq_type',
            array(
                'header' => Mage::helper('db1_anymarket')->__('Type of Operation (Import/Emport)'),
                'index'  => 'nmq_type',
                'type' => 'options',
                'options' => array(
                    'IMP' => Mage::helper('db1_anymarket')->__('Import'),
                    'EXP' => Mage::helper('db1_anymarket')->__('Export'),
                )

            )
        );
        $this->addColumn(
            'nmq_table',
            array(
                'header' => Mage::helper('db1_anymarket')->__('Source table'),
                'index'  => 'nmq_table',
                'type' => 'options',
                'options' => array(
                    'ORDER' => Mage::helper('db1_anymarket')->__('Order'),
                    'PRODUCT' => Mage::helper('db1_anymarket')->__('Product'),
                )

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
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketqueue_Grid
     
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('anymarketqueue');
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
     * @param DB1_AnyMarket_Model_Anymarketqueue
     * @return string
     
     */
    public function getRowUrl($row)
    {
        return null;
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
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketqueue_Grid
     
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
     * @param DB1_AnyMarket_Model_Resource_Anymarketqueue_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return DB1_AnyMarket_Block_Adminhtml_Anymarketqueue_Grid
     
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
