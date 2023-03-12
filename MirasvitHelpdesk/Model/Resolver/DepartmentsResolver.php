<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-helpdesk-graph-ql
 * @version   1.0.4
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Sigma\MirasvitHelpdesk\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Mirasvit\Helpdesk\Model\DepartmentFactory as DepartmentFactory;

class DepartmentsResolver implements ResolverInterface
{
    private $departmentFactory;

    public function __construct(
        DepartmentFactory $departmentFactory
    ) {
        $this->departmentFactory = $departmentFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $store = $context->getExtensionAttributes()->getStore();

        $departmentCollection = $this->departmentFactory->create()
            ->getPreparedCollection($store)
            ->addFieldToFilter('is_show_in_frontend', true);


        $result = [];
        /** @var \Mirasvit\Helpdesk\Model\Department $department */
        foreach ($departmentCollection as $department) {
            $result[] = [
                'department_id' => $department->getDepartmentId(),
                'name' => $department->getName(),
            ];
        }

        return $result;
    }
}
