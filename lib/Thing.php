<?php

/**
 * Class Thing
 *
 * This is a so called Entity class for storing and validating data. I find working with objects generally more
 * convenient so I'm adding this here even if it's not being used much.
 */
class Thing
{
    private $rawData = [];

    protected $id;
    protected $author;
    protected $title;

    public static $allowIdZero = true;

    /**
     * Thing constructor.
     * @param array|null $rawData
     */
    function __construct(array $rawData = null)
    {
        if (is_array($rawData)) {

            $expectedFieldsCount = count(ThingsMapper::$map);
            $fieldsCount = count($rawData);

            if ($expectedFieldsCount != $fieldsCount) {
                throw new InvalidArgumentException('Invalid number of fields: ' . $fieldsCount
                    . '. Expected: ' . $expectedFieldsCount);
            }

            $this->setId($rawData[ThingsMapper::$map['id']]);
            $this->setAuthor($rawData[ThingsMapper::$map['author']]);
            $this->setTitle($rawData[ThingsMapper::$map['title']]);

            $this->rawData = $rawData;
        }
    }

    /**
     * Returns array of values in the same order as they were in CSV file
     *
     * @return array
     */
    public function getValues()
    {
        return [
            ThingsMapper::$map['id'] => $this->getId(),
            ThingsMapper::$map['author'] => $this->getAuthor(),
            ThingsMapper::$map['title'] => $this->getTitle(),
        ];
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        /*
         * I am trying to prevent situations where the function would accept a float or a formatted number like 100 000,20
         * and just silently convert it to integer. Because presence of such numbers would indicate our CSV data is invalid
         * There cannot be a float id or id formatted to locale.
         */
        if (!is_numeric($id) || $id < 0 || strpos($id, '.') !== false || strpos($id, ',') !== false || strpos($id,
                ' ') !== false || !self::$allowIdZero && $id == 0 || is_null($id) || $id === "" || $id < 0) {

            $idRequirements = (self::$allowIdZero) ? 'a non negative integer' : 'a positive integer';
            throw new InvalidArgumentException('ID must be ' . $idRequirements);
        }

        $this->id = $id;
        return $this;
    }

    /**
     * @param string $author
     * @return $this
     */
    public function setAuthor($author)
    {
        if (empty($author)) {
            throw new InvalidArgumentException('Author cannot be empty');
        }

        $this->author = $author;
        return $this;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        if (empty($title)) {
            throw new InvalidArgumentException('Title cannot be empty');
        }

        $this->title = $title;
        return $this;
    }
}
