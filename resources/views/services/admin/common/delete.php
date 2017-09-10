<div class="alert -warning">
    <h4>Вы собираетесь удалить элемент</h4>
</div>
<form action="" method="POST">
    <input type="hidden" name="remove" value="1"/>
    <a href="<?= \Spirit\Request\URL::path(); ?>" class="btn">Отмена</a> <input type="submit" value="Удалить"
                                                                             class="btn -danger">
</form>