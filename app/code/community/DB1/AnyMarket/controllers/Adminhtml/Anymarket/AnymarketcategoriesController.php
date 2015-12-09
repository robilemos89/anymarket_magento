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
 * Anymarket Categories admin controller
 *
 * @category    DB1
 * @package     DB1_AnyMarket

 */
class DB1_AnyMarket_Adminhtml_Anymarket_AnymarketcategoriesController extends DB1_AnyMarket_Controller_Adminhtml_AnyMarket
{
    /**
     * init the anymarket categories
     *
     * @access protected
     * @return DB1_AnyMarket_Model_Anymarketcategories
     */
    protected function _initAnymarketcategories()
    {
        $anymarketcategoriesId  = (int) $this->getRequest()->getParam('id');
        $anymarketcategories    = Mage::getModel('db1_anymarket/anymarketcategories');
        if ($anymarketcategoriesId) {
            $anymarketcategories->load($anymarketcategoriesId);
        }
        Mage::register('current_anymarketcategories', $anymarketcategories);
        return $anymarketcategories;
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
             ->_title(Mage::helper('db1_anymarket')->__('Anymarket Categories'));

        $this->renderLayout();
    }


    /**
     * import Orders action
     *
     * @access public
     * @return void

     */
    public function sincCategsAction()
    {
        $storeID = Mage::getSingleton('core/session')->getStoreCategVariable();
        Mage::app()->setCurrentStore($storeID);
        Mage::helper('db1_anymarket/category')->getCategories();

        Mage::getSingleton('adminhtml/session')->addSuccess(
            Mage::helper('db1_anymarket')->__('Successfully synchronized categories.')
        );
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
     * edit anymarket categories - action
     *
     * @access public
     * @return void

     */
    public function editAction()
    {
        $anymarketcategoriesId    = $this->getRequest()->getParam('id');
        $anymarketcategories      = $this->_initAnymarketcategories();
        if ($anymarketcategoriesId && !$anymarketcategories->getId()) {
            $this->_getSession()->addError(
                Mage::helper('db1_anymarket')->__('This anymarket categories no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }
        $data = Mage::getSingleton('adminhtml/session')->getAnymarketcategoriesData(true);
        if (!empty($data)) {
            $anymarketcategories->setData($data);
        }
        Mage::register('anymarketcategories_data', $anymarketcategories);
        $this->loadLayout();
        $this->_title(Mage::helper('db1_anymarket')->__('AnyMarket'))
             ->_title(Mage::helper('db1_anymarket')->__('Anymarket Categories'));
        if ($anymarketcategories->getId()) {
            $this->_title($anymarketcategories->getNmcCatDesc());
        } else {
            $this->_title(Mage::helper('db1_anymarket')->__('Add anymarket categories'));
        }
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
    }

    /**
     * new anymarket categories action
     *
     * @access public
     * @return void

     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * save anymarket categories - action
     *
     * @access public
     * @return void

     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost('anymarketcategories')) {
            try {
                $anymarketcategories = $this->_initAnymarketcategories();
                $anymarketcategories->addData($data);
                $anymarketcategories->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('db1_anymarket')->__('Anymarket Categories was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $anymarketcategories->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setAnymarketcategoriesData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('db1_anymarket')->__('There was a problem saving the anymarket categories.')
                );
                Mage::getSingleton('adminhtml/session')->setAnymarketcategoriesData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('db1_anymarket')->__('Unable to find anymarket categories to save.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete anymarket categories - action
     *
     * @access public
     * @return void

     */
    public function deleteAction()
    {
        if ( $this->getRequest()->getParam('id') > 0) {
            try {
                $anymarketcategories = Mage::getModel('db1_anymarket/anymarketcategories');
                $anymarketcategories->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('db1_anymarket')->__('Anymarket Categories was successfully deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('db1_anymarket')->__('There was an error deleting anymarket categories.')
                );
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                Mage::logException($e);
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('db1_anymarket')->__('Could not find anymarket categories to delete.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * mass delete anymarket categories - action
     *
     * @access public
     * @return void

     */
    public function massDeleteAction()
    {
        $anymarketcategoriesIds = $this->getRequest()->getParam('anymarketcategories');
        if (!is_array($anymarketcategoriesIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('db1_anymarket')->__('Please select anymarket categories to delete.')
            );
        } else {
            try {
                foreach ($anymarketcategoriesIds as $anymarketcategoriesId) {
                    $anymarketcategories = Mage::getModel('db1_anymarket/anymarketcategories');
                    $anymarketcategories->setId($anymarketcategoriesId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('db1_anymarket')->__('Total of %d anymarket categories were successfully deleted.', count($anymarketcategoriesIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('db1_anymarket')->__('There was an error deleting anymarket categories.')
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
        $anymarketcategoriesIds = $this->getRequest()->getParam('anymarketcategories');
        if (!is_array($anymarketcategoriesIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('db1_anymarket')->__('Please select anymarket categories.')
            );
        } else {
            try {
                foreach ($anymarketcategoriesIds as $anymarketcategoriesId) {
                $anymarketcategories = Mage::getSingleton('db1_anymarket/anymarketcategories')->load($anymarketcategoriesId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d anymarket categories were successfully updated.', count($anymarketcategoriesIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('db1_anymarket')->__('There was an error updating anymarket categories.')
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
        $fileName   = 'anymarketcategories.csv';
        $content    = $this->getLayout()->createBlock('db1_anymarket/adminhtml_anymarketcategories_grid')
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
        $fileName   = 'anymarketcategories.xls';
        $content    = $this->getLayout()->createBlock('db1_anymarket/adminhtml_anymarketcategories_grid')
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
        $fileName   = 'anymarketcategories.xml';
        $content    = $this->getLayout()->createBlock('db1_anymarket/adminhtml_anymarketcategories_grid')
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
        return Mage::getSingleton('admin/session')->isAllowed('system/db1_anymarket/anymarketcategories');
    }
}
