<?php

namespace Xigen\UsernameLogin\Model\Attribute\Backend;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Username class
 */
class Username extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Generate and set unique Username to customer
     * @param Customer $object
     * @return void
     */
    protected function checkUniqueUsername($object)
    {
        $attribute = $this->getAttribute();
        $entity = $attribute->getEntity();
        $attributeValue = $object->getData($attribute->getAttributeCode());
        $increment = null;
        while (!$entity->checkAttributeUniqueValue($attribute, $object)) {
            throw new NoSuchEntityException(__('Account with Username is already exist'));
        }
    }

    /**
     * Make username unique before save
     * @param Customer $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $this->checkUniqueUsername($object);
        return parent::beforeSave($object);
    }
}
