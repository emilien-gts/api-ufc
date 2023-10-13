<?php

namespace App\Synchronizer\Helper;

use Doctrine\ORM\EntityManagerInterface;

class SynchronizerHelper
{
    public function __construct(
        private readonly EntityManagerInterface $em
    ) {
    }

    public function deleteEntity(string $className): void
    {
        $this->em->createQueryBuilder()->delete($className, 'e')->getQuery()->execute();
    }

    public function flushAndClear(): void
    {
        $this->em->flush();
        $this->em->clear();
    }
}
