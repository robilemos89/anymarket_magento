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
 * Anymarket Attributes admin controller
 *
 * @category    DB1
 * @package     DB1_AnyMarket

 */
class DB1_AnyMarket_Adminhtml_Anymarket_AnymarketattributesController extends DB1_AnyMarket_Controller_Adminhtml_AnyMarket
{
    /**
     * init the anymarket attributes
     *
     * @access protected
     * @return DB1_AnyMarket_Model_Anymarketattributes
     */
    protected function _initAnymarketattributes()
    {
        $anymarketattributesId  = (int) $this->getRequest()->getParam('id');
        $anymarketattributes    = Mage::getModel('db1_anymarket/anymarketattributes');
        if ($anymarketattributesId) {
            $anymarketattributes->load($anymarketattributesId);
        }
        Mage::register('current_anymarketattributes', $anymarketattributes);
        return $anymarketattributes;
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
        $this->_title(Mage::helper('db1_anymarket')->__('AnyMarket'))
             ->_title(Mage::helper('db1_anymarket')->__('Anymarket Attributes'));
        $this->renderLayout();
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
     * edit anymarket attributes - action
     *
     * @access public
     * @return void
     
     */
    public function editAction()
    {
        $anymarketattributesId    = $this->getRequest()->getParam('id');
        $anymarketattributes      = $this->_initAnymarketattributes();
        if ($anymarketattributesId && !$anymarketattributes->getId()) {
            $this->_getSession()->addError(
                Mage::helper('db1_anymarket')->__('This anymarket attributes no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }
        $data = Mage::getSingleton('adminhtml/session')->getAnymarketattributesData(true);
        if (!empty($data)) {
            $anymarketattributes->setData($data);
        }
        Mage::register('anymarketattributes_data', $anymarketattributes);
        $this->loadLayout();
        $this->_title(Mage::helper('db1_anymarket')->__('AnyMarket'))
             ->_title(Mage::helper('db1_anymarket')->__('Anymarket Attributes'));
        if ($anymarketattributes->getId()) {
            $this->_title($anymarketattributes->getNmaDesc());
        } else {
            $this->_title(Mage::helper('db1_anymarket')->__('Add anymarket attributes'));
        }
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
    }

    /**
     * new anymarket attributes action
     *
     * @access public
     * @return void
     
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * save anymarket attributes - action
     *
     * @access public
     * @return void
     
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost('anymarketattributes')) {
            try {
                $anymarketattributes = $this->_initAnymarketattributes();
                $anymarketattributes->addData($data);
                $anymarketattributes->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('db1_anymarket')->__('Anymarket Attributes was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $anymarketattributes->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setAnymarketattributesData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('db1_anymarket')->__('There was a problem saving the anymarket attributes.')
                );
                Mage::getSingleton('adminhtml/session')->setAnymarketattributesData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('db1_anymarket')->__('Unable to find anymarket attributes to save.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete anymarket attributes - action
     *
     * @access public
     * @return void
     
     */
    public function deleteAction()
    {
        if ( $this->getRequest()->getParam('id') > 0) {
            try {
                $anymarketattributes = Mage::getModel('db1_anymarket/anymarketattributes');
                $anymarketattributes->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('db1_anymarket')->__('Anymarket Attributes was successfully deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('db1_anymarket')->__('There was an error deleting anymarket attributes.')
                );
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                Mage::logException($e);
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('db1_anymarket')->__('Could not find anymarket attributes to delete.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * mass delete anymarket attributes - action
     *
     * @access public
     * @return void
     
     */
    public function massDeleteAction()
    {
        $anymarketattributesIds = $this->getRequest()->getParam('anymarketattributes');
        if (!is_array($anymarketattributesIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('db1_anymarket')->__('Please select anymarket attributes to delete.')
            );
        } else {
            try {
                foreach ($anymarketattributesIds as $anymarketattributesId) {
                    $anymarketattributes = Mage::getModel('db1_anymarket/anymarketattributes');
                    $anymarketattributes->setId($anymarketattributesId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('db1_anymarket')->__('Total of %d anymarket attributes were successfully deleted.', count($anymarketattributesIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('db1_anymarket')->__('There was an error deleting anymarket attributes.')
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
        $anymarketattributesIds = $this->getRequest()->getParam('anymarketattributes');
        if (!is_array($anymarketattributesIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('db1_anymarket')->__('Please select anymarket attributes.')
            );
        } else {
            try {
                foreach ($anymarketattributesIds as $anymarketattributesId) {
                $anymarketattributes = Mage::getSingleton('db1_anymarket/anymarketattributes')->load($anymarketattributesId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d anymarket attributes were successfully updated.', count($anymarketattributesIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('db1_anymarket')->__('There was an error updating anymarket attributes.')
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
        $fileName   = 'anymarketattributes.csv';
        $content    = $this->getLayout()->createBlock('db1_anymarket/adminhtml_anymarketattributes_grid')
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
        $fileName   = 'anymarketattributes.xls';
        $content    = $this->getLayout()->createBlock('db1_anymarket/adminhtml_anymarketattributes_grid')
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
        $fileName   = 'anymarketattributes.xml';
        $content    = $this->getLayout()->createBlock('db1_anymarket/adminhtml_anymarketattributes_grid')
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
        return Mage::getSingleton('admin/session')->isAllowed('system/db1_anymarket/anymarketattributes');
    }
}
