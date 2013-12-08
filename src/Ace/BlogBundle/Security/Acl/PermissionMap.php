<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marie
 * Date: 26/11/13
 * Time: 23:14
 * To change this template use File | Settings | File Templates.
 */

namespace Ace\BlogBundle\Security\Acl;

use Symfony\Component\Security\Acl\Permission\BasicPermissionMap;

class PermissionMap extends BasicPermissionMap
{
    const PERMISSION_DENY = 'DENY';
    const PERMISSION_PUBLISH = 'PUBLISH';
    const PERMISSION_UNPUBLISH = 'UNPUBLISH';

    public function __construct()
    {
        parent::__construct();

        $this->map[self::PERMISSION_DENY] = array(
            MaskBuilder::MASK_DENY,
        );

        $this->map[self::PERMISSION_PUBLISH] = array(
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER,
        );

        $this->map[self::PERMISSION_UNPUBLISH] = array(
            MaskBuilder::MASK_OPERATOR,
            MaskBuilder::MASK_MASTER,
            MaskBuilder::MASK_OWNER,
        );
    }
}