<form method="get" id="reloadFileForm">
    <input type="text" name="file" id="file" value="<?php print htmlspecialchars($file, ENT_QUOTES); ?>">
    <div>
    <input type="checkbox" id="skipfirstline" name="skipfirstline" value="true" <?php print $flChecked; ?>>
    <label for="skipfirstline">First line contains column names</label>
    </div>
    <div>
        <input type="checkbox" id="debug" name="debug" value="1" <?php print $debugChecked; ?>>
        <label for="debug">Display errors</label>
    </div>
    <input name="reload" type="submit" value="Load">
</form>