<?php

namespace App\EventListener;


use App\Entity\User;

use Doctrine\Persistence\Event\LifecycleEventArgs;

class UserAgeUpdate
{
    public function prePersist(LifecycleEventArgs $args): void
    {
        $this->updateAge($args);
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $this->updateAge($args);
    }

    private function updateAge(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        // Vérifier que l'objet est une instance de User
        if (!$entity instanceof User) {
            return;
        }

        // Calculer l'âge
        if ($entity->getDateOfBirth()) {
            $age = (new \DateTime())->diff($entity->getDateOfBirth())->y;
            $entity->setAge($age);
        }
    }
}