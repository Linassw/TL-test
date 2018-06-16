<?php if (!empty($errors)): ?>
    <h3>Errors encountered while parsing file <?php print htmlspecialchars($file, ENT_QUOTES); ?></h3>
    <em>(Note that if the first line contains column names it may not be displayed in the first table so in that case
        CSV file line number and table row number does not match)</em>
    <ul class="errors">
        <?php foreach ($errors as $error): ?>
            <li><?php print htmlspecialchars($error, ENT_QUOTES); ?></li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <h3>No errors encountered while parsing file <?php print htmlspecialchars($file, ENT_QUOTES); ?></h3>
<?php endif; ?>
