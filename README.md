yii2-gridview-editable
======================

A simple Extension to Yii2 GridView to enable simple editable columns

 GridViewEditable extends the standard Yii2 GridView to give very basic
  text editing.  It does not provide dropdowns, checkboxes etc, just 
  text fields.
 
  To make a Column editable you have to assign it to the class 'editColumn'
 	[
 	    'value'=>'caption',
 	    'contentOptions'=>['class'=>'editColumn']
 	],
 
  If your GridView is using filters, GridViewEditable will get the fieldname to 
  update from the filter field.
  
  Alternatively, you can specify the field name in the column definition
 	[
 	    'value'=>'caption',
 	    'contentOptions'=>['class'=>'editColumn', 'data-column'=>'caption']
 	],
 
  Full use example:-
  view:
  use app\common\GridViewEditable;
 
  GridViewEditable::widget([
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
  
  You have to define an action in your controller that receives $_POST data like this:
   public function actionUpdate($id)
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
  
