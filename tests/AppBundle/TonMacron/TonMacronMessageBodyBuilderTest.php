<?php

namespace Tests\AppBundle\TonMacron;

use AppBundle\Entity\TonMacronChoice;
use AppBundle\Entity\TonMacronFriendInvitation;
use AppBundle\Repository\TonMacronChoiceRepository;
use AppBundle\TonMacron\TonMacronMessageBodyBuilder;
use Ramsey\Uuid\Uuid;

class TonMacronMessageBodyBuilderTest extends \PHPUnit_Framework_TestCase
{
    private $repository;

    public function testBuildMessageBody()
    {
        $introductionText = <<<'EOF'
<p>
Bonjour %friend_first_name%,
<br/>
Comme tu le sais, les élections présidentielles auront lieu le 23 avril et 7 mai prochain.
</p>
EOF;
        $conclusionText = <<<'EOF'
<p>
Dis-moi ce que tu en penses. Tu trouveras tous les détails de ces propositions ici.
</p>
EOF;

        $this
            ->repository
            ->expects($this->once())
            ->method('findMailIntroduction')
            ->willReturn($this->createChoice(0, $introductionText));

        $this
            ->repository
            ->expects($this->once())
            ->method('findMailConclusion')
            ->willReturn($this->createChoice(0, $conclusionText));

        $this->assertSame(
            file_get_contents(__DIR__.'/../../Fixtures/files/campaign/ton_macron.txt'),
            $this->createBuilder()->buildMessageBody($this->createInvitation())
        );
    }

    private function createInvitation(): TonMacronFriendInvitation
    {
        $invitation = new TonMacronFriendInvitation(Uuid::uuid4(), 'Béatrice', 32, 'female');
        $invitation->setFriendEmailAddress('beatrice123@domain.tld');
        $invitation->setMailSubject('Toujours envie de voter blanc ?');
        $invitation->setAuthor('Marie', 'Dupont', 'marie.dupont@gmail.tld');

        $invitation->addChoice($this->createArgumentChoice(1, [
            "Pour augmenter le pouvoir d'achat, il propose de supprimer les cotisations.",
            'Tous les 5 ans, en cas de démission, tu auras le droit de bénéficier du chômage.',
        ]));
        $invitation->addChoice($this->createArgumentChoice(2, [
            'Si tu veux investir dans une PME, tu ne seras pas taxé.',
        ]));
        $invitation->addChoice($this->createArgumentChoice(3, [
            "Il lancera un grand plan de transformation agricole de 5 milliards d'euros.",
            'Il soutiendra la mise en place d’un système de subventions contracycliques de la PAC.',
        ]));
        $invitation->addChoice($this->createArgumentChoice(3, [
            'Il créera un « Pass Culture ».',
            'Les bibliothèques seront ouvertes en soirée et le week-end.',
        ]));
        $invitation->addChoice($this->createArgumentChoice(4, [
            'Emmanuel Macron est différent des responsables politiques.',
            "Emmanuel Macron n'est jamais seul.",
        ]));

        return $invitation;
    }

    private function createArgumentChoice(int $step, array $measures): TonMacronChoice
    {
        $content = [];
        foreach ($measures as $measure) {
            $content[] = sprintf('<li>%s</li>', $measure);
        }

        return $this->createChoice($step, implode("\n", $content));
    }

    private function createChoice(int $step, string $content): TonMacronChoice
    {
        return new TonMacronChoice(
            $uuid = Uuid::uuid4(),
            $step,
            $uuid->getLeastSignificantBitsHex(),
            md5($uuid->toString()),
            $content
        );
    }

    private function createBuilder(): TonMacronMessageBodyBuilder
    {
        return new TonMacronMessageBodyBuilder(
            new \Twig_Environment(new \Twig_Loader_Filesystem(__DIR__.'/../../Fixtures/views')),
            $this->repository
        );
    }

    protected function setUp()
    {
        parent::setUp();

        $this->repository = $this->createMock(TonMacronChoiceRepository::class);
    }

    protected function tearDown()
    {
        $this->repository = null;

        parent::tearDown();
    }
}
