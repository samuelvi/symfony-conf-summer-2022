<?php declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\CommentMessage;
use App\Repository\CommentRepository;
use App\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Workflow\WorkflowInterface;

#[AsMessageHandler(priority: 10)]
class CommentMessageHandler implements MessageHandlerInterface
{
    public function __construct(private EntityManagerInterface $entityManager,
                                private SpamChecker            $spamChecker,
                                private CommentRepository      $commentRepository,

                                #[Target('state_machine.comment')]
                                private WorkflowInterface      $commentStateMachine,
                                private MessageBusInterface    $bus,
                                private ?LoggerInterface       $logger = null,
                                private MailerInterface        $mailer,
                                #[Autowire('admin@admin.admin')]
                                private readonly string $adminEmail)
    {

    }

public
function __invoke(CommentMessage $message)
{
    $comment = $this->commentRepository->find($message->id);
    if (!$comment) {
        return;
    }

    if ($this->commentStateMachine->can($comment, 'accept')) {
        $score = $this->spamChecker->getSpamScore($comment, $message->context);
        $transition = match ($score) {
            2 => 'reject_spam',
            1 => 'might_be_spam',
            0 => 'accept',
        };

        $this->commentStateMachine->apply($comment, $transition);
        $this->entityManager->flush();

        $this->mailer->send((new NotificationEmail())
            ->subject('New comment posted')
            ->htmlTemplate('emails/comment_notification.html.twig')
            ->from($this->adminEmail)
            ->to($this->adminEmail)
            ->context(['comment' => $comment])
        );

        // Reentraría en este handler
        $this->bus->dispatch($message);
    } elseif ($this->logger) {
        $this->logger->debug('Dropping comment message', ['comment' => $comment->getId(), 'state' => $comment->getState()]);
    }

    $this->entityManager->flush();
}
}