<?php declare(strict_types=1);

namespace SchoolPlugin\Core\Content\SchoolParentInvitation;

use SchoolPlugin\Core\Content\School\SchoolEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;

class SchoolParentInvitationEntity extends Entity
{
    protected string $schoolId;

    protected ?string $parentName = null;

    protected string $email;

    protected ?SchoolEntity $school = null;

    public function getSchoolId(): string
    {
        return $this->schoolId;
    }

    public function setSchoolId(string $schoolId): void
    {
        $this->schoolId = $schoolId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getSchool(): ?SchoolEntity
    {
        return $this->school;
    }

    public function setSchool(?SchoolEntity $school): void
    {
        $this->school = $school;
    }

    public function getParentName(): ?string
    {
        return $this->parentName;
    }

    public function setParentName(?string $parentName): void
    {
        $this->parentName = $parentName;
    }
}