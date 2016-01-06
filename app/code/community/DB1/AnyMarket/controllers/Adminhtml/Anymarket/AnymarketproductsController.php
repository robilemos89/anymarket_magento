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
 * @copyright      Copyright (c) 25
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Anymarket Products admin controller
 *
 * @category    DB1
 * @package     DB1_AnyMarket
 */
class DB1_AnyMarket_Adminhtml_Anymarket_AnymarketproductsController extends DB1_AnyMarket_Controller_Adminhtml_AnyMarket
{
    /**
     * init the anymarket products
     *
     * @access protected
     * @return DB1_AnyMarket_Model_Anymarketproducts
     */
    protected function _initAnymarketproducts()
    {
        $anymarketproductsId  = (int) $this->getRequest()->getParam('id');
        $anymarketproducts    = Mage::getModel('db1_anymarket/anymarketproducts');
        if ($anymarketproductsId) {
            $anymarketproducts->load($anymarketproductsId);
        }
        Mage::register('current_anymarketproducts', $anymarketproducts);
        return $anymarketproducts;
    }

    /**
     * default action
     *
     * @access public
     * @return void
     * 
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_title(Mage::helper('db1_anymarket')->__('AnyMarket'))
             ->_title(Mage::helper('db1_anymarket')->__('Anymarket Products'));
        $this->renderLayout();
    }

    /**
     * sinc products action
     *
     * @access public
     * @return void
     */
    public function sincProdsAction()
    {
        $typeSincProd = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_type_prod_sync_field', Mage::app()->getStore()->getId());
        if($typeSincProd == 1){
            Mage::helper('db1_anymarket/product')->getProdsFromAnyMarket();
        }else{
            $products = Mage::getModel('catalog/product')->getCollection();
            foreach($products as $product) {

                $anymarketproducts = Mage::getModel('db1_anymarket/anymarketproducts')->load($product->getId(), 'nmp_id');
                if($anymarketproducts->getData('nmp_id') != null){
                    if( strtolower($anymarketproducts->getData('nmp_status_int')) != 'integrado'){

                        $ProdLoaded = Mage::getModel('catalog/product')->load( $product->getId() );
                        if( ($ProdLoaded->getStatus() == 1) && ($ProdLoaded->getData('integra_anymarket') == 1) ){
                            $amProd = Mage::helper('db1_anymarket/product');
                            $amProd->sendProductToAnyMarket( $product->getId() );

                            $filter = strtolower(Mage::getStoreConfig('anymarket_section/anymarket_attribute_group/anymarket_preco_field', Mage::app()->getStore()->getId()));
                            $ProdStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);

                            $amProd->updatePriceStockAnyMarket($product->getId(), $ProdStock->getQty(), $ProdLoaded->getData($filter));
                        }
                    }
                }
                
            }
        }

        Mage::getSingleton('adminhtml/session')->addSuccess(
            Mage::helper('db1_anymarket')->__('Produtos sincronizados com sucesso.')
        );
        $this->_redirect('*/*/');

    }

    /**
     * list products action
     *
     * @access public
     * @return void
     */
    public function listProdsAction()
    {
        $storeID = Mage::getSingleton('core/session')->getStoreListProdVariable();
        Mage::app()->setCurrentStore($storeID);
        Mage::helper('db1_anymarket/product')->massUpdtProds();
        $this->_redirect('*/*/');

    }

    /**
     * grid action
     *
     * @access public
     * @return void
     * 
     */

    public function gridAction()
    {
        $this->loadLayout()->renderLayout();
    }

    /**
     * edit anymarket products - action
     *
     * @access public
     * @return void
     * 
     */
    public function editAction()
    {
        $anymarketproductsId    = $this->getRequest()->getParam('id');
        $anymarketproducts      = $this->_initAnymarketproducts();
        if ($anymarketproductsId && !$anymarketproducts->getId()) {
            $this->_getSession()->addError(
                Mage::helper('db1_anymarket')->__('This anymarket products no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }
        $data = Mage::getSingleton('adminhtml/session')->getAnymarketproductsData(true);
        if (!empty($data)) {
            $anymarketproducts->setData($data);
        }
        Mage::register('anymarketproducts_data', $anymarketproducts);
        $this->loadLayout();
        $this->_title(Mage::helper('db1_anymarket')->__('AnyMarket'))
             ->_title(Mage::helper('db1_anymarket')->__('Anymarket Products'));
        if ($anymarketproducts->getId()) {
            $this->_title($anymarketproducts->getNmpDescError());
        } else {
            $this->_title(Mage::helper('db1_anymarket')->__('Add anymarket products'));
        }
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
    }

    /**
     * new anymarket products action
     *
     * @access public
     * @return void
     * 
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * save anymarket products - action
     *
     * @access public
     * @return void
     * 
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost('anymarketproducts')) {
            try {
                $anymarketproducts = $this->_initAnymarketproducts();
                $anymarketproducts->addData($data);
                $anymarketproducts->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('db1_anymarket')->__('Anymarket Products was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $anymarketproducts->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setAnymarketproductsData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('db1_anymarket')->__('There was a problem saving the anymarket products.')
                );
                Mage::getSingleton('adminhtml/session')->setAnymarketproductsData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('db1_anymarket')->__('Unable to find anymarket products to save.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete anymarket products - action
     *
     * @access public
     * @return void
     * 
     */
    public function deleteAction()
    {
        if ( $this->getRequest()->getParam('id') > 0) {
            try {
                $anymarketproducts = Mage::getModel('db1_anymarket/anymarketproducts');
                $anymarketproducts->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('db1_anymarket')->__('Anymarket Products was successfully deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('db1_anymarket')->__('There was an error deleting anymarket products.')
                );
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                Mage::logException($e);
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('db1_anymarket')->__('Could not find anymarket products to delete.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * mass delete anymarket products - action
     *
     * @access public
     * @return void
     * 
     */
    public function massDeleteAction()
    {
        $anymarketproductsIds = $this->getRequest()->getParam('anymarketproducts');
        if (!is_array($anymarketproductsIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('db1_anymarket')->__('Please select anymarket products to delete.')
            );
        } else {
            try {
                foreach ($anymarketproductsIds as $anymarketproductsId) {
                    $anymarketproducts = Mage::getModel('db1_anymarket/anymarketproducts');
                    $anymarketproducts->setId($anymarketproductsId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('db1_anymarket')->__('Total of %d anymarket products were successfully deleted.', count($anymarketproductsIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('db1_anymarket')->__('There was an error deleting anymarket products.')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }


    /**
     * import anymarket products - action
     *
     * @access public
     * @return void
     * 
     */
    public function importProdSincAction()
    {
        $anymarketproductsIds = $this->getRequest()->getParam('anymarketproducts');
        if (!is_array($anymarketproductsIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('db1_anymarket')->__('Por favor selecione os produtos para importar.')
            );
        } else {
            $typeSincProd = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_type_prod_sync_field', Mage::app()->getStore()->getId());
            if($typeSincProd == 1){
                foreach ($anymarketproductsIds as $anymarketproductsId) {
                    $anymarketproducts = Mage::getModel('db1_anymarket/anymarketproducts');
                    $anymarketproducts->load($anymarketproductsId);
                    Mage::helper('db1_anymarket/queue')->addQueue($anymarketproducts->getNmpId(), 'IMP', 'PRODUCT');
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('db1_anymarket')->__('Total %d products were added to the queue.', count($anymarketproductsIds))
                );
            }else{
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('db1_anymarket')->__('Integration Products "AnyMarket for Magento" marked as NOT')
                );
            }
        }
        $this->_redirect('*/*/index');
    }


    /**
     * export magento products - action
     *
     * @access public
     * @return void
     * 
     */
    public function exportProdSincAction()
    {
        $anymarketproductsIds = $this->getRequest()->getParam('anymarketproducts');
        if (!is_array($anymarketproductsIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('db1_anymarket')->__('Por favor selecione os produtos para exportar.')
            );
        } else {
            $typeSincProd = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_type_prod_sync_field', Mage::app()->getStore()->getId());            
            if($typeSincProd == 0){
                foreach ($anymarketproductsIds as $anymarketproductsId) {
                    $anymarketproducts = Mage::getModel('db1_anymarket/anymarketproducts');
                    $anymarketproducts->load($anymarketproductsId);
                    Mage::helper('db1_anymarket/queue')->addQueue($anymarketproducts->getNmpId(), 'EXP', 'PRODUCT');
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('db1_anymarket')->__('Total %d products were added to the queue.', count($anymarketproductsIds))
                );
            }else{
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('db1_anymarket')->__('Integration Products "Magento for Anymarket" marked as NOT')
                );
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass sincronize anymarket products - action
     *
     * @access public
     * @return void
     * 
     */
    public function massSincProductAction()
    {
        $anymarketproductsIds = $this->getRequest()->getParam('anymarketproducts');
        if (!is_array($anymarketproductsIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('db1_anymarket')->__('Please select the products to synchronize.')
            );
        }else{
            foreach ($anymarketproductsIds as $anymarketproductsId) {
                $anymarketproducts = Mage::getModel('db1_anymarket/anymarketproducts');
                $anymarketproducts->load($anymarketproductsId);

                if(is_array($anymarketproducts->getStoreId())){
                    $arrValueStore = array_values($anymarketproducts->getStoreId());
                    $storeID = array_shift($arrValueStore);
                }else{
                    $storeID = $anymarketproducts->getStoreId();
                }

                Mage::app()->setCurrentStore($storeID);
                $typeSincProd = Mage::getStoreConfig('anymarket_section/anymarket_integration_prod_group/anymarket_type_prod_sync_field', $storeID);
                if($typeSincProd == 1){
                    Mage::helper('db1_anymarket/queue')->addQueue($anymarketproducts->getNmpId(), 'IMP', 'PRODUCT');
                }else{
                    Mage::helper('db1_anymarket/queue')->addQueue($anymarketproducts->getNmpId(), 'EXP', 'PRODUCT'); 
                }
            }
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('db1_anymarket')->__('Total %d products were added to the queue.', count($anymarketproductsIds))
            );
        }

        $this->_redirect('*/*/index');
    }

    /**
     * mass status change - action
     *
     * @access public
     * @return void
     * 
     */
    public function massStatusAction()
    {
        $anymarketproductsIds = $this->getRequest()->getParam('anymarketproducts');
        if (!is_array($anymarketproductsIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('db1_anymarket')->__('Please select anymarket products.')
            );
        } else {
            try {
                foreach ($anymarketproductsIds as $anymarketproductsId) {
                    $anymarketproducts = Mage::getModel('db1_anymarket/anymarketproducts');
                    $anymarketproducts->load($anymarketproductsId);

                    $anymarketproducts->setStatus('1')->setIsMassupdate(true)->save();

                    $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $anymarketproducts->getNmpSku());
                    if($product){
                        $productID = $product->getId();

                        $product->setIntegraAnymarket( $this->getRequest()->getParam('status') );
                        $product->save();
                    }
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d anymarket products were successfully updated.', count($anymarketproductsIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('db1_anymarket')->__('There was an error updating anymarket products.')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * export as csv - action
     *
     * @access public
     * @return void
     * 
     */
    public function exportCsvAction()
    {
        $fileName   = 'anymarketproducts.csv';
        $content    = $this->getLayout()->createBlock('db1_anymarket/adminhtml_anymarketproducts_grid')
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export as MsExcel - action
     *
     * @access public
     * @return void
     * 
     */
    public function exportExcelAction()
    {
        $fileName   = 'anymarketproducts.xls';
        $content    = $this->getLayout()->createBlock('db1_anymarket/adminhtml_anymarketproducts_grid')
            ->getExcelFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export as xml - action
     *
     * @access public
     * @return void
     * 
     */
    public function exportXmlAction()
    {
        $fileName   = 'anymarketproducts.xml';
        $content    = $this->getLayout()->createBlock('db1_anymarket/adminhtml_anymarketproducts_grid')
            ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Check if admin has permissions to visit related pages
     *
     * @access protected
     * @return boolean
     * 
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/db1_anymarket/anymarketproducts');
    }
}
