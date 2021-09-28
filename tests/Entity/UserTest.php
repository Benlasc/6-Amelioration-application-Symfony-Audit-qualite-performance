<?php
namespace App\Tests\Entity;

use App\Entity\User;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserTest extends KernelTestCase
{

    public function getEntity(): User
    {
        return (new User())
            ->setEmail('user@domain.com')
            ->setUsername('User')
            ->setPassword('123456');
    }

    public function assertHasErrors(User $user, int $number = 0)
    {
        self::bootKernel();

        $errors = static::getContainer()->get(ValidatorInterface::class)->validate($user);
        $messages = [];

        /** @var ConstraintViolation $error */
        foreach ($errors as $error) {
            $messages[]= $error->getPropertyPath() . '=>' . $error->getMessage();
        }
        $this->assertCount($number, $errors, implode(', ', $messages));
    }

    public function testValidEntity()
    {
        $this->assertHasErrors($this->getEntity(), 0);
    }

    public function testInvalidMailEntity()
    {
        $this->assertHasErrors($this->getEntity()->setEmail("invalidMail@"), 1);
    }

    public function testInvalidBlankEntity()
    {
        $this->assertHasErrors($this->getEntity()->setEmail("")->setUsername(""), 2);
    }

    // Mail already used
    public function testInvalidUsedMailEntity()
    {
        $databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();

        $databaseTool->loadAliceFixture([
            __DIR__ . '/UserTestFixtures.yaml', 
        ]);

        $this->assertHasErrors($this->getEntity()->setEmail("user1@domain.fr"), 1);
    }
}
