<?php
$this->title = Yii::t('backend', 'Create admin');
?>
<?= $this->render('_menu', ['text' => isset($text) ? $text : '']) ?>
<?= $this->render('_form', ['model' => $model]) ?>