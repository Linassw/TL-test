<h2>Data from <?php print $source; ?></h2>
<table>
    <tr>
        <th>ID</th>
        <th>Author</th>
        <th>Title</th>
    </tr>
    <?php foreach($things as $thing): ?>
    <tr>
        <td><?php print htmlspecialchars(trim($thing[ThingsMapper::$map['id']]), ENT_QUOTES); ?></td>
        <td><?php print htmlspecialchars(trim($thing[ThingsMapper::$map['author']]), ENT_QUOTES); ?></td>
        <td><?php print htmlspecialchars(trim($thing[ThingsMapper::$map['title']]), ENT_QUOTES); ?></td>
    </tr>
    <?php endforeach; ?>
</table>