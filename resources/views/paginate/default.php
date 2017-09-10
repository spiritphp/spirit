<div class="paginate">

    <? if (isset($simple)): ?>
        <div class="paginate__simple">
            <ul>
                <li<?= $simple['prev']['disabled'] ? ' class="paginate__page-disabled"' : ''; ?>>
                    <a href="<?= $simple['prev']['link']; ?>">
                        &larr;
                    </a>
                </li>

                <li<?= $simple['next']['disabled'] ? ' class="paginate__page-disabled"' : ''; ?>>
                    <a href="<?= $simple['next']['link']; ?>">
                        &rarr;
                    </a>
                </li>
            </ul>
        </div>
    <? endif; ?>

    <? if (isset($pages) && count($pages)): ?>

        <div class="paginate__pages">
            <ul>
                <? if (!$edge['first']['disabled']): ?>
                    <li>
                        <a href="<?= $edge['first']['link']; ?>">
                            &#171;&#171;&#171;
                        </a>
                    </li>
                <? endif; ?>

                <? foreach ($pages as $page): ?>
                    <li<?= $page['disabled'] ? ' class="paginate__page-disabled"' : ''; ?>>
                        <a href="<?= $page['link']; ?>">
                            <?= $page['page']; ?>
                        </a>
                    </li>
                <? endforeach; ?>

                <? if (!$edge['last']['disabled']): ?>
                    <li>
                        <a href="<?= $edge['last']['link']; ?>">
                            &#187;&#187;&#187;
                        </a>
                    </li>
                <? endif; ?>
            </ul>
        </div>

    <? endif; ?>

</div>