<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="user-form">

    <?php $form = ActiveForm::begin(['id' => 'sign-up','action' => ['site/login']]); ?>
    
    <input type="hidden" name="create">
    <input type="hidden" name="is_subscriber" value="1" />
    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'required' => 'required']) ?>

    <?= $form->field($model, 'email')->input('email', ['required' => 'required']) ?>

    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true, 'required' => 'required']) ?>

    <?= $form->field($model, 'phone')->textInput(['maxlength' => true, 'required' => 'required']) ?>
    <p id="err" class="text-danger" style="display: none;">Invalid Email</p>
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Sign Up' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>