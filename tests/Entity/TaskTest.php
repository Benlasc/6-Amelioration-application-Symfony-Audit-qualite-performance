<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TaskTest extends KernelTestCase
{
    public function getEntity(): Task
    {
        return (new Task())
            ->setTitle('Title1')
            ->setCreatedAt(new DateTime())
            ->setContent('Contenu1');
    }

    public function assertHasErrors(Task $task, int $number = 0)
    {
        self::bootKernel();

        $errors = static::getContainer()->get(ValidatorInterface::class)->validate($task);
        $messages = [];

        /** @var ConstraintViolation $error */
        foreach ($errors as $error) {
            $messages[] = $error->getPropertyPath().'=>'.$error->getMessage();
        }
        $this->assertCount($number, $errors, implode(', ', $messages));
    }

    public function testValidEntity()
    {
        $this->assertHasErrors($this->getEntity(), 0);
    }

    public function testInvalidBlankEntity()
    {
        $this->assertHasErrors($this->getEntity()->setTitle('')->setContent(''), 2);
    }
}
