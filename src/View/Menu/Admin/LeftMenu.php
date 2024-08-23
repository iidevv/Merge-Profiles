<?php

namespace Iidev\MergeProfiles\View\Menu\Admin;

use XCart\Extender\Mapping\Extender;

/**
 * Left menu widget
 *
 * @Extender\Mixin
 */
class LeftMenu extends \XLite\View\Menu\Admin\LeftMenu
{
    /**
     * Returns the list of related targets
     *
     * @param string $target Target name
     *
     * @return array
     */
    public function getRelatedTargets($target)
    {
        $targets = parent::getRelatedTargets($target);

        if ('profile_list' == $target) {
            $targets[] = 'merge_profiles';
        }

        return $targets;
    }

    /**
     * Define items
     *
     * @return array
     */
    protected function defineItems()
    {
        $list = parent::defineItems();

        if (isset($list['store_setup'])) {
            $list['store_setup'][static::ITEM_CHILDREN]['merge_profiles'] = [
                static::ITEM_TITLE  => static::t('Merge profiles'),
                static::ITEM_TARGET => 'merge_profiles',
                static::ITEM_WEIGHT => 950,
            ];
        }

        return $list;
    }
}
