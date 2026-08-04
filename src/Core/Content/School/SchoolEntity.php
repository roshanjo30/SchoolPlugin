<?php declare(strict_types=1);

namespace SchoolPlugin\Core\Content\School;

use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Content\Media\MediaEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;

class SchoolEntity extends Entity
{
    use EntityIdTrait;

    protected string $schoolName;

    protected string $contactPerson;

    protected string $email;

    protected ?string $phone = null;

    protected ?string $street = null;

    protected ?string $zipcode = null;

    protected ?string $city = null;

    protected ?string $countryId = null;

    protected ?string $logoMediaId = null;

    protected ?MediaEntity $logoMedia = null;

    protected ?string $comment = null;

    protected ?string $categoryId = null;

    protected ?string $parentCategoryId = null;

    protected string $status;

    public function getSchoolName(): string
    {
        return $this->schoolName;
    }

    public function setSchoolName(string $schoolName): void
    {
        $this->schoolName = $schoolName;
    }

    public function getContactPerson(): string
    {
        return $this->contactPerson;
    }

    public function setContactPerson(string $contactPerson): void
    {
        $this->contactPerson = $contactPerson;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): void
    {
        $this->street = $street;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode(?string $zipcode): void
    {
        $this->zipcode = $zipcode;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getCountryId(): ?string
    {
        return $this->countryId;
    }

    public function setCountryId(?string $countryId): void
    {
        $this->countryId = $countryId;
    }

    public function getLogoMediaId(): ?string
    {
        return $this->logoMediaId;
    }

    public function setLogoMediaId(?string $logoMediaId): void
    {
        $this->logoMediaId = $logoMediaId;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getLogoMedia(): ?MediaEntity
    {
        return $this->logoMedia;
    }

    public function setLogoMedia(?MediaEntity $logoMedia): void
    {
        $this->logoMedia = $logoMedia;
    }

    public function getCategoryId(): ?string
    {
        return $this->categoryId;
    }

    public function setCategoryId(?string $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function getParentCategoryId(): ?string
    {
        return $this->parentCategoryId;
    }

    public function setParentCategoryId(?string $parentCategoryId): void
    {
        $this->parentCategoryId = $parentCategoryId;
    }

}