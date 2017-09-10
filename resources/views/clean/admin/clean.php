<? if ($success): ?>
    <div class="alert alert-success">
        <?= $success; ?>
    </div>
<? endif; ?>
    <h3>Очистка директорий</h3>
<?= $table_dir; ?>

    <hr/>

    <h3>Очистка Memcache</h3>
<?= $table_mcache; ?>