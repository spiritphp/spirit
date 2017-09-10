<div class="b-body">
    <?= $menu; ?>
    <? if ($success): ?>
        <div class="alert alert-success">
            Изменения сохранены
        </div>
    <? endif; ?>
    <?= $form; ?>
</div>
