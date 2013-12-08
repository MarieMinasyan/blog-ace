<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marie
 * Date: 26/11/13
 * Time: 23:12
 * To change this template use File | Settings | File Templates.
 */

namespace Ace\BlogBundle\Security\Acl;

use Symfony\Component\Security\Acl\Permission\MaskBuilder as BaseMaskBuilder;

class MaskBuilder extends BaseMaskBuilder
{
    const MASK_DENY = 256; // 1 << 8
    const MASK_PUBLISH = 512; // 1 << 9
    const MASK_UNPUBLISH = 1024; // 1 << 10
}