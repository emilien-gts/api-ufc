<?php

namespace App\Synchronizer\Helper;

use App\Synchronizer\Exception\SynchronizerException;
use Doctrine\ORM\EntityManagerInterface;

readonly class SynchronizerHelper
{
    public function __construct(
        private EntityManagerInterface $em
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

    public function createDatetimeFromUfcFormat(string $date, bool $throw = false): ?\DateTimeImmutable
    {
        $pattern = '/^(Jan(uary)?|Feb(ruary)?|Mar(ch)?|Apr(il)?|May|Jun(e)?|Jul(y)?|Aug(ust)?|Sep(tember)?|Oct(ober)?|Nov(ember)?|Dec(ember)?)\s\d{1,2},\s\d{4}$/';
        if (1 === \preg_match($pattern, $date)) {
            $date = \DateTimeImmutable::createFromFormat('M d, Y', $date);
            if (false === $date || $throw) {
                throw new SynchronizerException('Date format not supported');
            }

            return $date;
        }

        if ($throw) {
            throw new SynchronizerException(\sprintf('Date %s not supported', $date));
        }

        return null;
    }
}
