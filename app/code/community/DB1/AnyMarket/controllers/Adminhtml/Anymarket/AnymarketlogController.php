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
 * AnyMarket Log admin controller
 *
 * @category    DB1
 * @package     DB1_AnyMarket

 */
class DB1_AnyMarket_Adminhtml_Anymarket_AnymarketlogController extends DB1_AnyMarket_Controller_Adminhtml_AnyMarket
{
    /**
     * init the anymarket log
     *
     * @access protected
     * @return DB1_AnyMarket_Model_Anymarketlog
     */
    protected function _initAnymarketlog()
    {
        $anymarketlogId  = (int) $this->getRequest()->getParam('id');
        $anymarketlog    = Mage::getModel('db1_anymarket/anymarketlog');
        if ($anymarketlogId) {
            $anymarketlog->load($anymarketlogId);
        }
        Mage::register('current_anymarketlog', $anymarketlog);
        return $anymarketlog;
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
             ->_title(Mage::helper('db1_anymarket')->__('AnyMarket Log'));
        $this->renderLayout();
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
     * edit anymarket log - action
     *
     * @access public
     * @return void
     * 
     */
    public function editAction()
    {
        $anymarketlogId    = $this->getRequest()->getParam('id');
        $anymarketlog      = $this->_initAnymarketlog();
        if ($anymarketlogId && !$anymarketlog->getId()) {
            $this->_getSession()->addError(
                Mage::helper('db1_anymarket')->__('This anymarket log no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }
        $data = Mage::getSingleton('adminhtml/session')->getAnymarketlogData(true);
        if (!empty($data)) {
            $anymarketlog->setData($data);
        }
        Mage::register('anymarketlog_data', $anymarketlog);
        $this->loadLayout();
        $this->_title(Mage::helper('db1_anymarket')->__('AnyMarket'))
             ->_title(Mage::helper('db1_anymarket')->__('AnyMarket Log'));
        if ($anymarketlog->getId()) {
            $this->_title($anymarketlog->getLogDesc());
        } else {
            $this->_title(Mage::helper('db1_anymarket')->__('Add anymarket log'));
        }
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
    }

    /**
     * new anymarket log action
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
     * save anymarket log - action
     *
     * @access public
     * @return void
     * 
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost('anymarketlog')) {
            try {
                $anymarketlog = $this->_initAnymarketlog();
                $anymarketlog->addData($data);
                $anymarketlog->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('db1_anymarket')->__('AnyMarket Log was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $anymarketlog->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setAnymarketlogData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('db1_anymarket')->__('There was a problem saving the anymarket log.')
                );
                Mage::getSingleton('adminhtml/session')->setAnymarketlogData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('db1_anymarket')->__('Unable to find anymarket log to save.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete anymarket log - action
     *
     * @access public
     * @return void
     * 
     */
    public function deleteAction()
    {
        if ( $this->getRequest()->getParam('id') > 0) {
            try {
                $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                $anymarketlog->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('db1_anymarket')->__('AnyMarket Log was successfully deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('db1_anymarket')->__('There was an error deleting anymarket log.')
                );
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                Mage::logException($e);
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('db1_anymarket')->__('Could not find anymarket log to delete.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * mass delete anymarket log - action
     *
     * @access public
     * @return void
     * 
     */
    public function massDeleteAction()
    {
        $anymarketlogIds = $this->getRequest()->getParam('anymarketlog');
        if (!is_array($anymarketlogIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('db1_anymarket')->__('Please select anymarket log to delete.')
            );
        } else {
            try {
                foreach ($anymarketlogIds as $anymarketlogId) {
                    $anymarketlog = Mage::getModel('db1_anymarket/anymarketlog');
                    $anymarketlog->setId($anymarketlogId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('db1_anymarket')->__('Total of %d anymarket log were successfully deleted.', count($anymarketlogIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('db1_anymarket')->__('There was an error deleting anymarket log.')
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
     * 
     */
    public function massStatusAction()
    {
        $anymarketlogIds = $this->getRequest()->getParam('anymarketlog');
        if (!is_array($anymarketlogIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('db1_anymarket')->__('Please select anymarket log.')
            );
        } else {
            try {
                foreach ($anymarketlogIds as $anymarketlogId) {
                $anymarketlog = Mage::getSingleton('db1_anymarket/anymarketlog')->load($anymarketlogId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d anymarket log were successfully updated.', count($anymarketlogIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('db1_anymarket')->__('There was an error updating anymarket log.')
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
        $fileName   = 'anymarketlog.csv';
        $content    = $this->getLayout()->createBlock('db1_anymarket/adminhtml_anymarketlog_grid')
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
        $fileName   = 'anymarketlog.xls';
        $content    = $this->getLayout()->createBlock('db1_anymarket/adminhtml_anymarketlog_grid')
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
        $fileName   = 'anymarketlog.xml';
        $content    = $this->getLayout()->createBlock('db1_anymarket/adminhtml_anymarketlog_grid')
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
        return Mage::getSingleton('admin/session')->isAllowed('system/db1_anymarket/anymarketlog');
    }
}
