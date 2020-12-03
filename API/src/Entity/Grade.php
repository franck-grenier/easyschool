<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\GradeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=GradeRepository::class)
 */
class Grade
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"student:read", "grade:created"})
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     * @Assert\NotBlank
     * @Assert\GreaterThanOrEqual(0)
     * @Assert\LessThanOrEqual(20)
     * @Groups({"student:read", "grade:created", "grade:write"})
     */
    private $grade;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Groups({"student:read", "grade:created", "grade:write"})
     */
    private $subject;

    /**
     * @ORM\ManyToOne(targetEntity=Student::class, inversedBy="grades")
     * @ORM\JoinColumn(nullable=false)
     */
    private $student;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGrade(): ?float
    {
        return $this->grade;
    }

    public function setGrade(float $grade): self
    {
        $this->grade = $grade;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): self
    {
        $this->student = $student;

        return $this;
    }
}
