<?php declare(strict_types = 1);

namespace App\Security;

use App\Model\Database\EntityManager;
use Nette\Security\AuthenticationException;
use Nette\Security\Authenticator;
use Nette\Security\Passwords;
use Nette\Security\SimpleIdentity;

final class UserAuthenticator implements Authenticator
{

    public const string DEFAULT_PASSWORD = 'Trogoderma2024';

    public function __construct(private EntityManager $entityManager, private Passwords $passwords)
    {
    }

    public function authenticate(string $username, string $password): SimpleIdentity
    {
        $row = $this->entityManager->getUserRepository()->findOneByUsername($username);
        if (!$row) {
            throw new AuthenticationException('User not found.');
        }

        if (!$this->passwords->verify($password, $row->getPassword())) {
            throw new AuthenticationException('Invalid password.');
        }

        return new SimpleIdentity(
            $row->getId(),
            $row->getRole()->getName(),
            ['name' => $row->getFullname(), 'herbarium' => $row->getHerbarium()->getId()],
        );
    }

    /**
     * Computes default password hash.
     */
    public function calculateHash(string $password = ''): string
    {
        if ($password === '') {
            return $this->calculateHash(self::DEFAULT_PASSWORD);
        }

        return $this->passwords->hash($password);
    }

//    public function changePassword(\Nette\Security\User $user, $formValues)
//    {
//        $userEntity = $this->em->getUserRepository()->find($user->getIdentity()->getId());
//        if ($userEntity === NULL || !$this->passwords->verify($formValues->password_old, $userEntity->getPasswordHash())) {
//            throw new InvalidArgumentException('Wrong current password');
//        }
//        $userEntity->setPassword($this->calculateHash($formValues->password_new));
//        $this->em->flush();
//    }

}
