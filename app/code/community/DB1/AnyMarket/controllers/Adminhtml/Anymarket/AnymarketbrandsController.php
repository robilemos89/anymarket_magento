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
 * Anymarketbrands admin controller
 *
 * @category    DB1
 * @package     DB1_AnyMarket

 */
class DB1_AnyMarket_Adminhtml_Anymarket_AnymarketbrandsController extends DB1_AnyMarket_Controller_Adminhtml_AnyMarket
{
    /**
     * init the anymarketbrands
     *
     * @access protected
     * @return DB1_AnyMarket_Model_Anymarketbrands
     */
    protected function _initAnymarketbrands()
    {
        $anymarketbrandsId  = (int) $this->getRequest()->getParam('id');
        $anymarketbrands    = Mage::getModel('db1_anymarket/anymarketbrands');
        if ($anymarketbrandsId) {
            $anymarketbrands->load($anymarketbrandsId);
        }
        Mage::register('current_anymarketbrands', $anymarketbrands);
        return $anymarketbrands;
    }

    /**
     * default action
     *
     * @access public
     * @return void
     
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_title(Mage::helper('db1_anymarket')->__('Anymarket'))
             ->_title(Mage::helper('db1_anymarket')->__('Anymarketbrand'));
        $this->renderLayout();
    }

    /**
     * import Brands action
     *
     * @access public
     * @return void
     */
    public function sincBrandsAction()
    {
        $storeID = Mage::getSingleton('core/session')->getStoreBrandVariable();
        $brandCount = Mage::helper('db1_anymarket/brand')->getBrands($storeID);

        if( $brandCount > 0 ) {
            Mage::getSingleton('adminhtml/session')->addSuccess(
                Mage::helper('db1_anymarket')->__('Successfully synchronized ').$brandCount. Mage::helper('db1_anymarket')->__(' brands.')
            );
        }else{
            Mage::getSingleton('adminhtml/session')->addError( Mage::helper('db1_anymarket')->__('No brand was synchronized.') );
        }
        $this->_redirect('*/*/');
    }

    /**
     * grid action
     *
     * @access public
     * @return void
     
     */
    public function gridAction()
    {
        $this->loadLayout()->renderLayout();
    }

    /**
     * edit anymarketbrands - action
     *
     * @access public
     * @return void
     
     */
    public function editAction()
    {
        $anymarketbrandsId    = $this->getRequest()->getParam('id');
        $anymarketbrands      = $this->_initAnymarketbrands();
        if ($anymarketbrandsId && !$anymarketbrands->getId()) {
            $this->_getSession()->addError(
                Mage::helper('db1_anymarket')->__('This brand no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }
        $data = Mage::getSingleton('adminhtml/session')->getAnymarketbrandsData(true);
        if (!empty($data)) {
            $anymarketbrands->setData($data);
        }
        Mage::register('anymarketbrands_data', $anymarketbrands);
        $this->loadLayout();
        $this->_title(Mage::helper('db1_anymarket')->__('Anymarket'))
             ->_title(Mage::helper('db1_anymarket')->__('Anymarketbrand'));
        if ($anymarketbrands->getId()) {
            $this->_title($anymarketbrands->getBrdId());
        } else {
            $this->_title(Mage::helper('db1_anymarket')->__('Add Brands'));
        }
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
    }

    /**
     * new anymarketbrands action
     *
     * @access public
     * @return void
     
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * save anymarketbrands - action
     *
     * @access public
     * @return void
     
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost('anymarketbrands')) {
            try {
                $anymarketbrands = $this->_initAnymarketbrands();
                $anymarketbrands->addData($data);
                $anymarketbrands->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('db1_anymarket')->__('Brand was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $anymarketbrands->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setAnymarketbrandsData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('db1_anymarket')->__('There was a problem saving the brand.')
                );
                Mage::getSingleton('adminhtml/session')->setAnymarketbrandsData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('db1_anymarket')->__('Unable to find brand to save.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete anymarketbrands - action
     *
     * @access public
     * @return void
     
     */
    public function deleteAction()
    {
        if ( $this->getRequest()->getParam('id') > 0) {
            try {
                $anymarketbrands = Mage::getModel('db1_anymarket/anymarketbrands');
                $anymarketbrands->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('db1_anymarket')->__('Brand was successfully deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('db1_anymarket')->__('There was an error deleting brand.')
                );
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                Mage::logException($e);
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('db1_anymarket')->__('Could not find brand to delete.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * mass delete anymarketbrands - action
     *
     * @access public
     * @return void
     
     */
    public function massDeleteAction()
    {
        $anymarketbrandsIds = $this->getRequest()->getParam('anymarketbrands');
        if (!is_array($anymarketbrandsIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('db1_anymarket')->__('Please select brand to delete.')
            );
        } else {
            try {
                foreach ($anymarketbrandsIds as $anymarketbrandsId) {
                    $anymarketbrands = Mage::getModel('db1_anymarket/anymarketbrands');
                    $anymarketbrands->setId($anymarketbrandsId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('db1_anymarket')->__('Total of %d brand were successfully deleted.', count($anymarketbrandsIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('db1_anymarket')->__('There was an error deleting brand.')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass status change - action
     *
     * @access public
     * @return void
     
     */
    public function massStatusAction()
    {
        $anymarketbrandsIds = $this->getRequest()->getParam('anymarketbrands');
        if (!is_array($anymarketbrandsIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('db1_anymarket')->__('Please select brands.')
            );
        } else {
            try {
                foreach ($anymarketbrandsIds as $anymarketbrandsId) {
                $anymarketbrands = Mage::getSingleton('db1_anymarket/anymarketbrands')->load($anymarketbrandsId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d brand were successfully updated.', count($anymarketbrandsIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('db1_anymarket')->__('There was an error updating brand.')
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
     
     */
    public function exportCsvAction()
    {
        $fileName   = 'anymarketbrands.csv';
        $content    = $this->getLayout()->createBlock('db1_anymarket/adminhtml_anymarketbrands_grid')
            ->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export as MsExcel - action
     *
     * @access public
     * @return void
     
     */
    public function exportExcelAction()
    {
        $fileName   = 'anymarketbrands.xls';
        $content    = $this->getLayout()->createBlock('db1_anymarket/adminhtml_anymarketbrands_grid')
            ->getExcelFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * export as xml - action
     *
     * @access public
     * @return void
     
     */
    public function exportXmlAction()
    {
        $fileName   = 'anymarketbrands.xml';
        $content    = $this->getLayout()->createBlock('db1_anymarket/adminhtml_anymarketbrands_grid')
            ->getXml();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Check if admin has permissions to visit related pages
     *
     * @access protected
     * @return boolean
     
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/db1_anymarket/anymarketbrands');
    }
}
