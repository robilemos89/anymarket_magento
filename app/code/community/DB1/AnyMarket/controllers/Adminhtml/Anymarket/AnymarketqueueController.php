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
 * Anymarket Queue admin controller
 *
 * @category    DB1
 * @package     DB1_AnyMarket
 */
class DB1_AnyMarket_Adminhtml_Anymarket_AnymarketqueueController extends DB1_AnyMarket_Controller_Adminhtml_AnyMarket
{
    /**
     * init the anymarket queue
     *
     * @access protected
     * @return DB1_AnyMarket_Model_Anymarketqueue
     */
    protected function _initAnymarketqueue()
    {
        $anymarketqueueId  = (int) $this->getRequest()->getParam('id');
        $anymarketqueue    = Mage::getModel('db1_anymarket/anymarketqueue');
        if ($anymarketqueueId) {
            $anymarketqueue->load($anymarketqueueId);
        }
        Mage::register('current_anymarketqueue', $anymarketqueue);
        return $anymarketqueue;
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
             ->_title(Mage::helper('db1_anymarket')->__('Anymarket Queues'));
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
     * edit anymarket queue - action
     *
     * @access public
     * @return void
     
     */
    public function editAction()
    {
        $anymarketqueueId    = $this->getRequest()->getParam('id');
        $anymarketqueue      = $this->_initAnymarketqueue();
        if ($anymarketqueueId && !$anymarketqueue->getId()) {
            $this->_getSession()->addError(
                Mage::helper('db1_anymarket')->__('This anymarket queue no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }
        $data = Mage::getSingleton('adminhtml/session')->getAnymarketqueueData(true);
        if (!empty($data)) {
            $anymarketqueue->setData($data);
        }
        Mage::register('anymarketqueue_data', $anymarketqueue);
        $this->loadLayout();
        $this->_title(Mage::helper('db1_anymarket')->__('AnyMarket'))
             ->_title(Mage::helper('db1_anymarket')->__('Anymarket Queues'));
        if ($anymarketqueue->getId()) {
            $this->_title($anymarketqueue->getNmqId());
        } else {
            $this->_title(Mage::helper('db1_anymarket')->__('Add anymarket queue'));
        }
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
    }

    /**
     * new anymarket queue action
     *
     * @access public
     * @return void
     
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * save anymarket queue - action
     *
     * @access public
     * @return void
     
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost('anymarketqueue')) {
            try {
                $anymarketqueue = $this->_initAnymarketqueue();
                $anymarketqueue->addData($data);
                $anymarketqueue->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('db1_anymarket')->__('Anymarket Queue was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $anymarketqueue->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setAnymarketqueueData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('db1_anymarket')->__('There was a problem saving the anymarket queue.')
                );
                Mage::getSingleton('adminhtml/session')->setAnymarketqueueData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('db1_anymarket')->__('Unable to find anymarket queue to save.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete anymarket queue - action
     *
     * @access public
     * @return void
     
     */
    public function deleteAction()
    {
        if ( $this->getRequest()->getParam('id') > 0) {
            try {
                $anymarketqueue = Mage::getModel('db1_anymarket/anymarketqueue');
                $anymarketqueue->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('db1_anymarket')->__('Anymarket Queue was successfully deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('db1_anymarket')->__('There was an error deleting anymarket queue.')
                );
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                Mage::logException($e);
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('db1_anymarket')->__('Could not find anymarket queue to delete.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * mass delete anymarket queue - action
     *
     * @access public
     * @return void
     
     */
    public function massDeleteAction()
    {
        $anymarketqueueIds = $this->getRequest()->getParam('anymarketqueue');
        if (!is_array($anymarketqueueIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('db1_anymarket')->__('Please select anymarket queues to delete.')
            );
        } else {
            try {
                foreach ($anymarketqueueIds as $anymarketqueueId) {
                    $anymarketqueue = Mage::getModel('db1_anymarket/anymarketqueue');
                    $anymarketqueue->setId($anymarketqueueId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('db1_anymarket')->__('Total of %d anymarket queues were successfully deleted.', count($anymarketqueueIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('db1_anymarket')->__('There was an error deleting anymarket queues.')
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
        $anymarketqueueIds = $this->getRequest()->getParam('anymarketqueue');
        if (!is_array($anymarketqueueIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('db1_anymarket')->__('Please select anymarket queues.')
            );
        } else {
            try {
                foreach ($anymarketqueueIds as $anymarketqueueId) {
                $anymarketqueue = Mage::getSingleton('db1_anymarket/anymarketqueue')->load($anymarketqueueId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d anymarket queues were successfully updated.', count($anymarketqueueIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('db1_anymarket')->__('There was an error updating anymarket queues.')
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
        $fileName   = 'anymarketqueue.csv';
        $content    = $this->getLayout()->createBlock('db1_anymarket/adminhtml_anymarketqueue_grid')
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
        $fileName   = 'anymarketqueue.xls';
        $content    = $this->getLayout()->createBlock('db1_anymarket/adminhtml_anymarketqueue_grid')
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
        $fileName   = 'anymarketqueue.xml';
        $content    = $this->getLayout()->createBlock('db1_anymarket/adminhtml_anymarketqueue_grid')
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
        return Mage::getSingleton('admin/session')->isAllowed('system/db1_anymarket/anymarketqueue');
    }
}
