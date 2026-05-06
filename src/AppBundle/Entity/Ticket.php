<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ticket")
 */
class Ticket
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    private $id;

    /** @ORM\Column(type="string", length=180) */
    private $siteKey;

    /** @ORM\Column(type="string", length=180) */
    private $name;

    /** @ORM\Column(type="string", length=180) */
    private $email;

    /** @ORM\Column(type="string", length=255) */
    private $subject;

    /** @ORM\Column(type="text") */
    private $description;

    /** @ORM\Column(type="string", length=40) */
    private $status = 'open';

    /** @ORM\Column(type="datetime") */
    private $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId() { return $this->id; }
    public function getSiteKey() { return $this->siteKey; }
    public function setSiteKey($siteKey) { $this->siteKey = $siteKey; return $this; }
    public function getName() { return $this->name; }
    public function setName($name) { $this->name = $name; return $this; }
    public function getEmail() { return $this->email; }
    public function setEmail($email) { $this->email = $email; return $this; }
    public function getSubject() { return $this->subject; }
    public function setSubject($subject) { $this->subject = $subject; return $this; }
    public function getDescription() { return $this->description; }
    public function setDescription($description) { $this->description = $description; return $this; }
    public function getStatus() { return $this->status; }
    public function setStatus($status) { $this->status = $status; return $this; }
    public function getCreatedAt() { return $this->createdAt; }
}
