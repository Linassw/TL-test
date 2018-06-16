<?php

interface Mapper
{
    public function getAll();
    public function getArray();
    public function saveAll(array $things);
}