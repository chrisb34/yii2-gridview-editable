<?php
/**
 * -----------------------------------------------------------------------------
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 * -----------------------------------------------------------------------------
 */

namespace common\components;

use yii\web\AssetBundle;
use Yii;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 *
 * @since 2.0
 *
 * Customized by Nenad Živković
 */
class gridViewEditableAsset extends AssetBundle
{
    public $sourcePath = '@common/components'; //@app/assets/'; 
    public $css = [
    ];
    public $js = [
        'js/gridViewEditable.js',
    ];
    public $depends = [
    ];
}
