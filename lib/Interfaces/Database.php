<?php

interface Database
{
    public function getAll($query);
    public function allowZeroInAutoIncrement();
    public function preparedQuery($query, array $params = null);
}