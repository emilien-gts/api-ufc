<?php

namespace App\Synchronizer\Contracts;

interface SynchronizerInterface
{
    public function sync(): void;
}
