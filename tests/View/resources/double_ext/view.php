<?
$__env = ext(__DIR__ . '/layout');
$__env->in('content');
    echo $value1;
    echo view(__DIR__ . '/double_view.php',['value' => $value2]);
$__env->save();
?>
