<?
use Spirit\Services\Table;

?>
    <table class="table table-striped">
        <thead class="thead-default">
        <tr>
            <? foreach ($columns as $value): ?>
                <th>
                    <?= $value; ?>
                </th>
            <? endforeach; ?>
        </tr>
        </thead>
        <tbody>
        <? if (count($items) == 0 && isset($columns)): ?>
            <td colspan="<?= count($columns); ?>" class="text-center">Данных нет</td>
        <? endif; ?>

        <? foreach ($items as $item): ?>

            <tr>
                <? foreach ($columns as $_columnKey => $_columnValue): ?>

                    <? if (!isset($item[$_columnKey])): ?>
                        <td>-</td>
                        <? continue; ?>
                    <? endif; ?>

                    <? $value = &$item[$_columnKey]; ?>
                    <td
                        <?= isset($value[Table::N_CLASS]) ? ' class="' . $value[Table::N_CLASS] . '"' : ''; ?>
                        <?= isset($value[Table::N_WIDTH]) ? ' style="width:' . $value[Table::N_WIDTH] . (is_numeric($value[Table::N_WIDTH]) ? '%' : '') . '"' : ''; ?>
                        <?= isset($value[Table::N_STYLE]) ? ' style="' . $value[Table::N_STYLE] . '"' : ''; ?>
                    >
                        <? if (isset($value[Table::N_LINK])): ?>
                            <a href="<?= $value[Table::N_LINK][0]; ?>"<?
                            if (isset($value[Table::N_LINK][1]) && $value[Table::N_LINK][1]) {
                                $attr = [];
                                foreach ($value[Table::N_LINK][1] as $k => $v) {
                                    $attr[] = $k . '="' . $v . '"';
                                }
                                echo ' ' . implode(' ', $attr);
                            }
                            ?>><?= $value['value']; ?></a>
                        <? else: ?>
                            <?= $value['value']; ?>
                        <? endif; ?>

                    </td>

                <? endforeach; ?>
            </tr>

        <? endforeach; ?>
        </tbody>
    </table>
<? if (isset($page)): ?>
    <?= $page; ?>
<? endif; ?>