<?php

class CSVHandler
{
    /** @var string */
    private $fileName;

    /** @var array */
    private $rawData = [];

    /** @var string */
    private $rawCSV = '';

    // Settings
    /** @var string */
    protected $separator = ';';

    /** @var string */
    protected $enclosure = '"';

    /** @var string */
    protected $templatesDir = 'templates';

    /** @var bool */
    protected $skipFirstLine = true; // set to false if your CSV file does not contain a line with column names

    // Data
    /** @var array */
    protected $things = [];

    /** @var array */
    protected $errors = [];

    // Database
    /** @var Mapper */
    protected $thingsMapper;


    /**
     * CSVHandler constructor.
     *
     * @param string $file
     * @param Mapper $mapper
     * @param bool $skipFirstLine
     * @throws InvalidArgumentException
     */
    public function __construct($file, Mapper $mapper, $skipFirstLine = false)
    {
        ini_set('auto_detect_line_endings', true); // apparently needed for CSV created on Macs
        ini_set('allow_url_fopen', false); // Just in a case. I don't know where it's gonna run.

        /*
        * Load the file
        */

        $this->fileName = $file;
        $this->thingsMapper = $mapper;
        $this->skipFirstLine = $skipFirstLine;

        if (filter_var($this->fileName, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid file path');
        }

        if (is_dir($this->fileName) || !is_readable($this->fileName)) {
            throw new InvalidArgumentException($file . ' does not exist');
        }

        $handler = fopen($this->fileName, 'r');
        $this->parseCSV($handler);
        fclose($handler);

        $this->rawCSV = file_get_contents($this->fileName);
    }

    /**
     * Displays the contents of a file in a textarea and also in a tabular format
     *
     * I am not sure if I understood this task correctly.
     * Please let me know if you expected something else I can always rewrite the code
     *
     * @throws Exception
     */
    public function show()
    {
        /*
        * Output the file into a textarea
        * Use the data to produce a html table
        */

        $flChecked = $this->skipFirstLine ? 'checked' : '';
        $debugChecked = (!empty($_GET['debug']) && $_GET['debug'] == 1) ? 'checked' : '';


        /*
         * Displays file data in a table as it is, only escaped for javascript shennanigans prevention. For example
         * it does display data that would be considered invalid for saving to DB.
         */
        $table = $this->makeTable($this->rawData, 'CSV file');


         //An input field to load a different file, and couple of checkboxes, simply for convenience.
        $fileInput = $this->render('show.php',
            ['file' => $this->fileName, 'flChecked' => $flChecked, 'debugChecked' => $debugChecked]);


        //File contents escaped and dumped into a textarea.
        $textarea = $this->render('textarea.php', ['rawCSVdata' => $this->rawCSV]);

        print $fileInput;
        print $textarea;
        print $table;
    }

    /**
     * Saves CSV data to a database
     *
     */
    public function import()
    {
        // I moved the connection data to lib/MySQLDatabase.php
        /*
        * Import data into a SQL database
        */

        $this->thingsMapper->saveAll($this->things);
    }

    /**
     *  Displays data from SQL database as an HTML table
     *
     *  I took liberty to rename the method to showTableFromDB() to make it immediately clear what it does
     *  and also to be consistent with another similar method that displays data: show()
     *
     *  I usually use "make.." or "create.." to only create and return something as opposed to display
     *  From my experience functions randomly sending things to browser outside dedicated display methods
     *  can increase debug time a lot
     *
     * @throws Exception
     */
    public function showTableFromDB()
    {
        /*
        * Read data from SQL database and output as html table again
        */

        /*
         * Displays all data from a db table, including previously imported files.
         */
        $things = $this->thingsMapper->getArray();
        $table = $this->makeTable($things, 'Database');
        print $table;
    }

    public function showErrors()
    {
        print $this->render('errors.php', ['errors' => $this->errors, 'file' => $this->fileName]);
    }


    /**
     * Generates an HTML table from a template and data array
     *
     *
     * @param array $things
     * @param string $source
     * @return string
     * @throws Exception
     */
    protected function makeTable(array $things, $source)
    {
        return $this->render('table.php', ['things' => $things, 'source' => $source]);
    }


    /**
     * Renders HTML using given template and parameters
     *
     * This is a very simple replacement of a "template engine". It's nowhere near the best solution and more like
     * a placeholder, in a real life situation I'd use something like Twig or whatever given framework uses.
     * But I don't want to add loads of external libraries for this simple test task, I have a feeling I'm already
     * overkilling it as it is.
     *
     * @param string $template a path to a template file
     * @param array $params
     * @return string HTML
     */
    protected function render($template, array $params)
    {
        $templatePath = $this->templatesDir . DIRECTORY_SEPARATOR . $template;

        if (!is_readable($templatePath) || is_dir($templatePath)) {
            throw new InvalidArgumentException('Invalid template file: ' . $templatePath);
        }

        ob_start();
        foreach ($params as $name => $value) {
            $$name = $value;
        }
        require $templatePath;

        return ob_get_clean();
    }

    /**
     * Parses a given CSV file into array of raw data and Thing objects
     *
     * Entity Objects are for convenience only, they are not really necessary for such a simple code strictly speaking,
     * and they may be harmful performance wise with huge data files. In this project so far they mostly used as a place to put
     * validation code. They could be really useful code maintenance and readability wise if this project would grow
     * larger as many real life projects tend to.
     *
     * Note on validation: In a real life situation I'd probably use something like cutplace tool https://pypi.org/project/cutplace/
     * (called from php via linux command line) to validate that not only file is a valid CSV but it also conforms to specification.
     * Here I'm simply checking the first line and we are good to go. Individual fields (id, author, title) are also
     * validated later against simple rules like i.e. id cannot be empty or negative, author cannot be empty, etc.
     *
     * @param resource $fh handler returned by fopen()
     */
    protected function parseCSV($fh)
    {
        $lineNo = 0;

        while (!feof($fh)) {

            $lineNo++; // because it starts at 1

            /*
                apparently fgetcsv() has some issues with quotes in my PHP version. In real life situation depending on
                the PHP version and CSV files it may be necessary to write in house CSV parser or find another solution.
            */
            $entry = fgetcsv($fh, 0, $this->separator, $this->enclosure);

            if ($lineNo == 1 && count($entry) != count(ThingsMapper::$map)) {
                /*
                * unexpected number of fields from the very beginning; might not even be CSV format
                * either an invalid file provided or something shady is going on here.
                 *
                 * With subsequent lines I am being more generous only skipping invalid lines but CONTINUING processing
                 * file simply so that I wouldn't need too many different CSV files for testing different cases. If needed
                 * it's trivial to change the code so that any invalid line would immediately terminate the script without
                 * anything being saved to a db.
                */
                throw new InvalidArgumentException('Unexpected number of fields, file processing terminated');
            }

            if ($this->skipFirstLine && $lineNo == 1) {
                ThingsMapper::$map = array_flip($entry);
                continue; // skipping the first line with column names
            }

            $this->rawData[] = $entry;

            try {
                $this->things[] = new Thing($entry);
            } catch (InvalidArgumentException $iae) {
                $this->errors[] = 'Line: ' . $lineNo . " Message: " . $iae->getMessage();
            }
        }
    }
}
