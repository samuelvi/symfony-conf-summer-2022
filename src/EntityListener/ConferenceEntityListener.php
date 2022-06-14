<?php declare(strict_types=1);

namespace App\EntityListener;

use App\Entity\Conference;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\String\Slugger\SluggerInterface;

// use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;

// #[AsEntityListener(event: Events::prePersist, entity: Conference::class)]
// #[AsEntityListener(event: Events::preUpdate, entity: Conference::class)]

#[AutoconfigureTag('doctrine.orm.entity_listener', ['event' => Events::prePersist, 'entity' => Conference::class])]
#[AutoconfigureTag('doctrine.orm.entity_listener', ['event' => Events::preUpdate, 'entity' => Conference::class])]
class ConferenceEntityListener
{
    public function __construct(private SluggerInterface $slugger)
    {
    }

    public function prePersist(Conference $conference, LifecycleEventArgs $event)
    {
        $conference->computeSlug($this->slugger);
    }

    public function preUpdate(Conference $conference, LifecycleEventArgs $event)
    {
        $conference->computeSlug($this->slugger);
    }
}