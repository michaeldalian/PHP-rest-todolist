<?php

/**
 * @ interfaces 
 */
interface iReadOperation
{
    public function getAllTodos(): array;
    public function getDetailById(int $id): array;
    public function searchTodos(array $query): array;
}
