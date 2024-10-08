<?php declare(strict_types = 1);

namespace App\Security;

use Nette\Security\SimpleIdentity;

class Identity extends SimpleIdentity
{

    public function getFullname(): string
    {
        return sprintf('%s %s', $this->data['name'] ?? '', $this->data['surname'] ?? '');
    }

}
