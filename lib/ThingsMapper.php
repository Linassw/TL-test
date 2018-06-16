<?php
require_once 'Interfaces/Mapper.php';

/**
 * Maps CSV to Database
 *
 * Class ThingsMapper
 */
class ThingsMapper implements Mapper
{
    private $db;
    private $tblName = 'testtable';

    private $columns = ['id' => 'thing_id', 'author' => 'thing_name', 'title' => 'thing_title'];
    public static $map = ['id' => 0, 'author' => 1, 'title' => 2]; // default column mapping if CSV did not provide any

    /**
     * ThingsMapper constructor.
     * @param Database $db
     */
    function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * @return array array of Thing objects
     */
    public function getAll()
    {
        $thingsDb = $this->getArray();
        $things = [];

        foreach ( $thingsDb as $thingData )
        {
            $thing = new Thing();
            $thing->setId($thingData[0])
                ->setAuthor($thingData[1])
                ->setTitle($thingData[2]);
            $things[] = $thing;
        }

        return $things;
    }

    /**
     * @return mixed
     */
    public function getArray()
    {
        $columns = [];
        asort(self::$map);

        foreach ( self::$map as $key => $place ) {
            $columns[] = $this->columns[$key];
        }

        return $this->db->getAll('SELECT ' . implode(', ', $columns). ' FROM ' . $this->tblName);
    }

    /**
     * Saves an array of Thing objects to a db
     *
     * On duplicate ID it updates the row. I am not sure if that is what you want or maybe it should throw an exception
     * or just ignore the row entirely. But it seemed to make most sense this way.
     *
     * There can be many different possibilities i.e. if you encounter the same id and the same author you can assume it's
     * the update to the same row; but when id is the same and author is different it may be a mistake so you don't
     * update but log an error instead. On the other hand I can also see a case where you do want to update author
     * for example because previously it was entered with a typo. So for now I'm just updating everything
     *
     * @param array $things
     * @return mixed
     */
    public function saveAll(array $things)
    {
        $values = [];
        $updateColumns = [];
        $data = [];

        foreach ( $things as $thing )
        {
            $valuesCount = count($thing->getValues());

            $placeholders = array_fill(0, $valuesCount, '?');
            $values[] = '(' . implode(', ', $placeholders) . ')';
            array_push($data, $thing->getId(), $thing->getAuthor(), $thing->getTitle());
        }

        foreach ( $this->columns as $column )
        {
            $updateColumns[] = $column . " = VALUES($column)";
        }

        $query = 'INSERT INTO ' . $this->tblName . ' (' . implode(', ', $this->columns) . ') VALUES '
            . implode(', ', $values)
            . '  ON DUPLICATE KEY UPDATE ' . implode(', ', $updateColumns);

        if ( Thing::$allowIdZero ) {
            $this->db->allowZeroInAutoIncrement();
        }

        return $this->db->preparedQuery($query, $data);
    }
}
