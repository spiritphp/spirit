<?

use Spirit\Services\Form;

?>

<? if ($error && count($error)): ?>
    <div class="alert alert-danger">
        <?= implode("<br/>\n", $error); ?>
    </div>
<? endif; ?>

<form<? foreach($form as $k => $v) echo ' ' . $k . '="' . $v . '"'; ?>>

    <? foreach($elements as $name => $item): ?>

        <? if ($item['type'] === Form::FORM_HIDDEN): ?>
            <input type="hidden" name="<?= $item['name']; ?>" value="<?= $item['value']; ?>"/>
            <? continue; ?>
        <? endif; ?>

        <div class="form-group">
            <? if ($item['label'] && !in_array($item['type'], [Form::FORM_CHECKBOX])): ?>
                <label><?= $item['label']; ?></label>
            <? endif; ?>
            <? if (!isset($item['type']) || $item['type'] === Form::FORM_TEXT): ?>
                <input type="text" class="form-control" <?= Form::attrHtml($item); ?> />
            <? elseif ($item['type'] === Form::FORM_PASSWORD): ?>
                <input type="password" class="form-control" <?= Form::attrHtml($item); ?> />
            <? elseif ($item['type'] === Form::FORM_NUMBER): ?>
                <input type="number" class="form-control" <?= Form::attrHtml($item); ?> />
            <? elseif ($item['type'] === Form::FORM_DATE): ?>
                <input type="date" class="form-control" <?= Form::attrHtml($item); ?> />
            <? elseif ($item['type'] === Form::FORM_DATETIME): ?>
                <input type="datetime" class="form-control" <?= Form::attrHtml($item); ?> />
            <? elseif ($item['type'] === Form::FORM_TEXTAREA): ?>
                <textarea class="form-control" <?= Form::attrHtml($item); ?>><?= $item['value']; ?></textarea>
            <? elseif ($item['type'] === Form::FORM_CAPTCHA): ?>
                <div class="captcha">
                    <img src="/captcha/<?= $item['options']['uniqueId']; ?>"/>
                </div>
                <input type="hidden" name="<?= $item['name']; ?>_captcha_uid"
                       value="<?= $item['options']['uniqueId']; ?>"/>
                <input type="text" class="form-control" <?= Form::attrHtml($item); ?> />

            <? elseif ($item['type'] === Form::FORM_SELECT): ?>
                <select class="form-control" <?= Form::attrHtml($item); ?>>
                    <? foreach($item[Form::OPTIONS]['values'] as $_k => $_v): ?>
                        <option value="<?= $_k; ?>" <?= $_k == $item[Form::VALUE] ? 'selected="selected"' : ''; ?>>
                            <?= $_v; ?>
                        </option>
                    <? endforeach; ?>
                </select>
            <? elseif ($item['type'] === Form::FORM_RADIO): ?>
                <? foreach($item['options']['values'] as $_k => $_v): ?>
                    <div class="radio">
                        <label>
                            <input name="<?= $item[Form::NAME]; ?>" type="radio"
                                   value="<?= $_k; ?>" <?= $_k == $item[Form::VALUE] ? 'checked="checked"' : ''; ?> />
                            <?= $_v; ?>
                        </label>
                    </div>
                <? endforeach; ?>
            <? elseif ($item['type'] === Form::FORM_CHECKBOX_MANY): ?>
                <? foreach($item['values'] as $_k => $_v): ?>
                    <div class="checkbox">
                        <label>
                            <input name="<?= $item[Form::NAME]; ?>[]" type="checkbox"
                                   value="<?= $_k; ?>" <?= ($_k == $item[Form::VALUE]) || (is_array($item[Form::VALUE]) && in_array($_k, $item[Form::VALUE])) ? 'checked="checked"' : ''; ?> />
                            <?= $_v; ?>
                        </label>
                    </div>
                <? endforeach; ?>
            <? elseif ($item['type'] === Form::FORM_CHECKBOX): ?>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" <?= Form::attrHtml($item); ?> />
                        <?= isset($item['label']) ? $item['label'] : ''; ?>
                    </label>
                </div>
            <? elseif ($item['type'] == Form::FORM_FILE): ?>
                <? if (isset($item['value']) && is_string($item['value'])): ?>
                    <div>
                        <a href="<?= $item['value']; ?>" class="btn btn-default">Скачать</a>
                    </div>
                <? endif; ?>
                <input type="file" <?= Form::attrHtml($item); ?> />
            <? elseif ($item['type'] === Form::FORM_SUBMIT): ?>
                <div class="text-right">
                    <input type="submit" class="btn btn-success" <?= Form::attrHtml($item); ?> value="<?= $item[Form::VALUE]; ?>"/>
                </div>
            <? elseif ($item['type'] === Form::FORM_BUTTON): ?>
                <div class="text-right">
                    <input type="button" class="btn btn-success" <?= Form::attrHtml($item); ?> value="<?= $item[Form::VALUE]; ?>"/>
                </div>
            <? endif; ?>

            <? if (isset($item[Form::ERROR])): ?>
                <div class="help-block text-danger">
                    <?= $item[Form::ERROR]; ?>
                </div>
            <? endif; ?>
        </div>

    <? endforeach; ?>

</form>