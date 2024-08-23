<?php

namespace Iidev\MergeProfiles\View;

use XCart\Extender\Mapping\ListChild;

/**
 * @ListChild (list="admin.center", zone="admin")
 */
class MergeProfiles extends \XLite\View\AView
{
    /**
     *
     * @return array
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();

        $list[] = 'modules/Iidev/MergeProfiles/css/style.css';

        return $list;
    }

    /**
     * @return array
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        $list[] = 'modules/Iidev/MergeProfiles/js/controller.js';

        return $list;
    }
    
    /**
     * @return array
     */
    public static function getAllowedTargets()
    {
        return array_merge(parent::getAllowedTargets(), ['merge_profiles']);
    }

    /**
     * @return string
     */
    protected function getDefaultTemplate()
    {
        return 'modules/Iidev/MergeProfiles/page/body.twig';
    }

}
