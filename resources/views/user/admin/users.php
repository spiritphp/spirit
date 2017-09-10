<h3>Пользователи</h3>
<div class="b-body">
<form action="<?= \Spirit\Request\URL::path(); ?>" method="GET" class="b-mbottom-20">
    <div class="input-group">

        <input type="text" class="form-control" name="search" value="<?= isset($search) ? $search : ''; ?>"
               placeholder="Поиск..."/>
        <span class="input-group-btn">
            <button class="btn btn-primary" type="submit">Мне повезёт</button>
        </span>
    </div>
</form>

<?= $table; ?>
</div>