<?= $nav; ?>
<div class="b-w960">
    <?= isset($table) ? $table : ''; ?>
</div>
<?= isset($file) ? '<pre class="trace" style="white-space: pre;">' . $file . '</pre>' : ''; ?>