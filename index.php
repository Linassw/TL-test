<?php
/*
 * Please write robust and secure code, we will use different test csv files in evaluating the code
 * We have set up a MySQL server to use for this project, the database layout can be found in the
 * sql file and the relevant data to access said server is set in the import() function
 */

require_once 'lib/CSVHandler.php'; // <--- I have moved the class here.
require_once 'lib/Thing.php';
require_once 'lib/ThingsMapper.php';
require_once 'lib/MySQLDatabase.php';

?><!DOCTYPE html>
<html>
<head>
    <title>CSV Handler</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
<?php
if(isset($_GET['file'])) {
    try {
        $thingsMapper = new ThingsMapper(new MySQLDatabase());
        $csvHandler = new CSVHandler($_GET['file'], $thingsMapper, $_GET['skipfirstline']);
        $csvHandler->show();
        $csvHandler->import();
        $csvHandler->showTableFromDB();

        if (!empty($_GET['debug']) && $_GET['debug'] == 1) {
            $csvHandler->showErrors();
        }
    } catch (Exception $e) {
        print $e->getMessage();
    }

} else {
	echo '<ul>';
	echo '<li><a href="?file=test.csv">test.csv</a></li>';
	echo '<li><a href="?file=test2.csv">test2.csv</a></li>';
	echo '</ul>';
}
?>
</body>
</html>