<?php

namespace App\Exception;

use App\Entity\Student;

class NoGradesException extends \Exception
{
    const NO_GRADES          = "No grades given to any students yet. So no global average grade available.";
    const STUDENT_NO_GRADES  = "Student %s %s has no grades yet. So no average grade available.";

    protected $student;

    public function __construct(Student $student = null)
    {
        $this->student = $student;
        parent::__construct($this->setMessage());
    }

    private function setMessage(): string
    {
        $message = (null != $this->student) ?
            sprintf($this::STUDENT_NO_GRADES, $this->student->getFirstname(), $this->student->getName()) :
            $this::NO_GRADES;

        return $message;
    }
}
