<?php

class Mbemro_Orderconfirmation_Block_Orderlist extends Mage_Core_Block_Template
{

    public function __construct()
    {
        parent::__construct();
		$_customer = Mage::getModel('customer/customer')->load(Mage::getSingleton('customer/session')->getCustomer()->getId());
		$corp_company = Mage::getResourceModel('eav/entity_attribute_collection')->setCodeFilter('corp_company_dd')->getFirstItem();
		$corp_department = Mage::getResourceModel('eav/entity_attribute_collection')->setCodeFilter('corp_department_dd')->getFirstItem();		
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$query = "SELECT ce.entity_id, ce.email, cev.value AS company, cev1.value AS department "
				."FROM customer_entity ce "
				."LEFT JOIN customer_entity_int cev ON ce.entity_id = cev.entity_id "
				."AND cev.attribute_id = ".$corp_company->getId()." "
				."LEFT JOIN customer_entity_int cev1 ON ce.entity_id = cev1.entity_id "
				."AND cev1.attribute_id = ".$corp_department->getId()." "
				."WHERE cev.value='".$_customer->getCorpCompanyDd()."' and cev1.value='".$_customer->getCorpDepartmentDd()."' ";
		$results = $readConnection->fetchAll($query);
		foreach($results as $row){
			$custIds[] = $row['entity_id'];
		}

        $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', array('in' => $custIds))
			->addFieldToFilter('state', array('eq' => 'holded'))
            ->setOrder('created_at', 'desc')
        ;

        $this->setOrders($orders);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock('page/html_pager', 'orderconfirmation.orderlist.pager')
            ->setCollection($this->getOrders());
        $this->setChild('pager', $pager);
        $this->getOrders()->load();
        return $this; 
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getViewUrl($order)
    {
        return $this->getUrl('*/*/view', array('order_id' => $order->getId()));
    }


    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }
}
