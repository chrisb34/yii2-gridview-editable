<?php
/**
 * GridViewEditable extends the standard Yii2 GridView to give very basic
 * text editing.  It does not provide dropdowns, checkboxes etc, just 
 * text fields.
 *
 * To make a Column editable you have to assign it to the class 'editColumn'
 *	[
 *	    'value'=>'caption',
 *	    'contentOptions'=>['class'=>'editColumn']
 *	],
 *
 * If your GridView is using filters, GridViewEditable will get the fieldname to 
 * update from the filter field.
 * 
 * Alternatively, you can specify the field name in the column definition
 *	[
 *	    'value'=>'caption',
 *	    'contentOptions'=>['class'=>'editColumn', 'data-column'=>'caption']
 *	],
 
 * Full use example:-
 * view:
 * use app\common\GridViewEditable;
 *
 * GridViewEditable::widget([
        'dataProvider' => $dataProvider,
	'updateUrl'=>'/controller/update/', // <= Points to an update Action (see below)
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
		'thumbnail',
		[
		    'value'=>'caption',
		    'contentOptions'=>['class'=>'editColumn', 'data-column'=>'caption']
		],
		'alt_text',
		'sort_order',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); 
 * 
 * You have to define an action in your controller that receives $_POST data like this:
 *  public function actionUpdate($id)
    {
        $model = $this->findModel($id);

	$model->load(Yii::$app->request->post());
	if ($model->save()) {
	    if (Yii::$app->getRequest()->isAjax)
		    return Json::encode (['status'=> true]);
		else 
		    return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }
 * 
 * 
 *
 * @author Chris Backhouse <support@chris-backhouse.com>
 * @package GridViewEditable
 * @licence: Open Source
 * @url: https://github.com/chrisb34/yii2-gridview-editable
 * @since 0.1
 */

namespace common\components;

use Yii;
use yii\helpers\Html;
use yii\web\View;
use yii\grid\gridview;
use common\components\gridViewEditableAsset;

class GridViewEditable extends \yii\grid\GridView {
    public $id;
    public $colorSuccess = '#5cb85c';
    public $colorFailure = '#FCB0B0';
    public $timeOut = 2000;
    public $includeKey = true;
    public $updateUrl = '';
    public $createUrl = '';
    public $refreshUrl = '';
    public $deleteUrl = '';
    public $isEditable = true;
    public $tableClass = 'div.grid-view table.table';
    public $otherPostData = '';
    public $options;

    public function init() {

        $this->options = [ 'id' => $this->id, 'class'=>'gridview'];
        $this->buildJS();
        parent::init();
    }

    public function buildJS() {
       $this->registerAssets();
       
       $view = $this->getView();
       $od = \json_encode($this->otherPostData);
       $ik = ($this->includeKey) ? 'true' : 'false';
       
       $js1 = " var gdv_{$this->id} = new editableGridview('gdv_{$this->id}'); ";
       $js2 = " 
            gdv_{$this->id}.tableClass = '{$this->tableClass}';
            gdv_{$this->id}.hisId = '#{$this->id}';
            gdv_{$this->id}.csrfToken = '".Yii::$app->getRequest()->getCsrfToken()."';
            gdv_{$this->id}.updateUrl = '{$this->updateUrl}';
            gdv_{$this->id}.createUrl = '{$this->createUrl}';
            gdv_{$this->id}.deleteUrl = '{$this->deleteUrl}';
            gdv_{$this->id}.refreshUrl = '{$this->refreshUrl}';
            gdv_{$this->id}.colorSuccess = '{$this->colorSuccess}';
            gdv_{$this->id}.colorFailure = '{$this->colorFailure}';
            gdv_{$this->id}.varTimeout = {$this->timeOut};
            gdv_{$this->id}.otherPostData = {$od};
            gdv_{$this->id}.includeKey = {$ik};
            gdv_{$this->id}.gridview = $('#{$this->id}');
        

            $('#{$this->id}').on('click', 'td.editColumn', function() {gdv_{$this->id}.drawEditor(this);});
            $('#{$this->id}').on('blur', ' td.editColumn', function() {gdv_{$this->id}.drawText(this);});
            $('#{$this->id}').on('click', '.add-new', function() {gdv_{$this->id}.addRow(this);});
            $('#{$this->id}').on('click', ' .editable-grid-newrow button', function(e) {
                e.preventDefault();
                gdv_{$this->id}.addRowSave($(this).closest('tr'));
                });
            $('#{$this->id}').on('click', ' a.delete', function(e) {
                e.preventDefault();
                gdv_{$this->id}.deleteRow(this);
            });

            \$grid = $('#{$this->id}');
            \$grid.data('name','gdv_{$this->id}');

        ";
       
        $view->registerJs($js1, View::POS_END);
        $view->registerJs($js2, View::POS_READY);

    }
    /**
     * Registers the needed assets
     */
    public function registerAssets()
    {
        $view = $this->getView();
        gridViewEditableAsset::register($view);
    }
}
