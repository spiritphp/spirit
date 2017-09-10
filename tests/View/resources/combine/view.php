<?
$__env = ext(__DIR__ . '/layout');
$__env->in('content1');
    echo $value1;
    $__env->prepend('content2');
        echo $value2;
    $__env->save();
$__env->save();
?>
