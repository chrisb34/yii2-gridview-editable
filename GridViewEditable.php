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
		    return Json::encode (['status'=> false, 'message'=>'Save Failed']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }
 * 
 *
 * @author Chris Backhouse <support@chris-backhouse.com>
 * @package GridViewEditable
 * @since 0.1
 */

namespace app\common;

use Yii;
use yii\helpers\Html;
use yii\grid\gridview;

class GridViewEditable extends \yii\grid\GridView {
	public $colorSuccess='#18CC00';
	public $colorFailure='#FCB0B0';
	public $timeOut=2000;
	public $updateUrl='';
        public $isEditable=true;
        
	public function init() {
    
	    $this->buildJS();
	    parent::init();
	}
	
	public function buildJS() {
	   $view = $this->getView();
	   $view->registerJs("
		    function drawEditor(el) {
			el=$(el);
			if (el.attr('class')!='editColumn' | el.children('.inplace-editor').size() > 0 ) return false; //|

			wd=el.width();
			txt=el.text();

			var t = $(\"<span class='inplace-editor'><textarea class='inEdit'/></textarea></span>\");
			el.html(t);

			var input = el.find('textarea');
			input
			    .val(txt)
			    .attr('rows',parseInt(el.height()/19))
			    .width(wd)
			    .focus()
			    .select();
		    }
		    function createTimeoutHandler(el) {
			return function() { el.attr('style', 'background: none'); };
		    }

		    function updateDb(el) {
			el=$(el);
			data=el.find('.inEdit').val();
			if (data==undefined) return false;
			idx=$(el).parent().index();
			col=el.index();
			//go up the DOM to the parent grid-view and then down the DOM to find the keys
			//key=$.fn.yiiGridView.getSelection('properties-grid')[0];  // this is tidy but relies on the row being selected
			//key=el.parents('.grid-view').find('.keys').children()[idx].textContent;
			key=$(el).parent().data('key');
		        //check to see if the grid has filters - then we can get the column names from there
			if ( $('#{$this->id} .filters').length ) {
			    cell=col=$('#".$this->id."').find('.filters').children()[col];
			    field=$(cell).children()[0].name;
			} else {
			    // otherwise we have to rely on the user defining the data-name in the column
			    field=el.data('column');
			}
			console.log('field name: '+field);
			var postData={};
			postData[field]=data;
			postData['".Yii::$app->getRequest()->getCsrfToken()."']='".Yii::$app->getRequest()->getCsrfToken()."';
			postData['json']=true;
			$.ajax({
			    url: '".$this->updateUrl."'+key,
			    data: $.param(postData),
			    type: 'POST'
			    })
			    .done(function(data) {
				console.log(data);
				pdata=$.parseJSON(data);
				if (pdata.status || pdata.status=='true' || pdata.status=='ok') {
				    el.attr('style','background: ".$this->colorSuccess."');
				    setTimeout( createTimeoutHandler(el), ".$this->timeOut.");
				} else {
				    el.attr('style','background: ".$this->colorFailure."');
				}
			    });
			return data;
		    }
		    function drawText(el) {
			el=$(el);
			if (el.children('.inplace-editor')) {
			    data=el.find('.inEdit').val();
			    if (data!=undefined) {
				d=updateDb(el);
				el.html( d );
			    }
			}
		    }

		    $('div.grid-view table.table td.editColumn').on('click', function() {drawEditor(this);});
		    $('div.grid-view table.table').on('blur', ' td.editColumn', function() {drawText(this);});

		    ",$view::POS_READY);

	}
}
