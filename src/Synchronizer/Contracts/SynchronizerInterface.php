<?php

namespace App\Synchronizer\Model;

interface SynchronizerInterface
{
    public function sync(): void;
}
