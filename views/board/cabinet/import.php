<?= Flash::render('global/flash') ?>
<?if(isset($errors)) echo View::factory('error/validation', array('errors'=>$errors))->render() ?>
<form action="" id="importForm" method="post" enctype="multipart/form-data" class="pure-form  pure-form-aligned">
    <div class="pure-g">
        <div class="pure-u-2-3">
            <fieldset>
                <legend>Импорт объявлений</legend>
                <div class="pure-control-group">
                    <label for="type">Раздел</label>
                    <select name="type" id="type">
                        <option value="realty">Недвижимость</option>
                    </select>
                </div>
                <div class="pure-control-group">
                    <label for="file" class="import-file-label">Файл (YML)</label>
                    <input type="file" name="feed" id="file">
                </div>
                <div class="pure-controls">
                    <input type="submit" value="Отправить" class="pure-button pure-button-primary">
                </div>
            </fieldset>
        </div>
        <div class="pure-u-1-3">
            <fieldset>
                <legend>Статистика объявлений</legend>
                <dl class="import-stats">
                    <dt>Максимально доступно</dt>
                    <dd><?php echo $stats['maxads']?></dd>
                    <dt>Загружено объявлений</dt>
                    <dd><?php echo $stats['loaded']?></dd>
                    <dt>Возможно загрузить</dt>
                    <dd><?php echo $stats['last']?></dd>
                </dl>
            </fieldset>
        </div>
    </div>
</form>