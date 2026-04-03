<?php

namespace App\Repositories;

interface PaluwaganRepositoryInterface
{
    public function getUserEntries(int $customerID);
    public function joinPackage(int $customerID, int $packageID);
    public function getAllEntriesByStatus(string $status);
}