<?php

namespace OuterEdge\LimitRole\Block\User\Edit\Tab;

use Magento\User\Block\User\Edit\Tab\Roles as MageRoles;

class Roles extends MageRoles
{
    /**
     *  authSession
     */
    protected $authSession;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory $userRolesFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Authorization\Model\ResourceModel\Role\CollectionFactory $userRolesFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_userRolesFactory = $userRolesFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->authSession = $authSession;
        $this->_resource = $resource;
        parent::__construct($context, $backendHelper, $jsonEncoder, $userRolesFactory, $coreRegistry, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_userRolesFactory->create();
        $collection->setRolesFilter();

        //geting role id of the current loged user.
        $current_user_role_id = $this->authSession->getUser()->getRole()->getId();

        $current_user_rule_permission = 'deny';
        $full_admin_roles = array();

        foreach ($this->getRulesArray() as $rulesWithAll) {
            //get the current loged in user permission.
            if ($rulesWithAll['role_id'] == $current_user_role_id) {
                $current_user_rule_permission = $rulesWithAll['permission'];
            }
            //geting array of roles which have full permission,this is disabled for this time to allow all the role to be hide from custom user.
            $full_admin_roles[] = $rulesWithAll['role_id'];

        }
        $this->setCollection($collection);
        //filter the available role list if the current user not have full admin permission.
        if ($current_user_rule_permission != 'allow') {
            $collection->addFieldToFilter('role_id', array("nin" => $full_admin_roles));
            //not returning to parent as, parent will remove the filters.
            return;
        }
        return parent::_prepareCollection();
    }

    /**
     *
     *  Collect all the rules that have full permission "allowed" values in the "authorization_rule" table.
     *
     * @return array
     */
    protected function getRulesArray()
    {

        $ruleTable = $this->_resource->getTableName("authorization_rule");
        $connection = $this->_resource->getConnection();
        $select = $connection->select()
            ->from(['r' => $ruleTable])
            ->where("`resource_id`='Magento_Backend::all' AND `permission`='allow'");
        return $rulesWithAllArr = $connection->fetchAll($select);
    }
}
