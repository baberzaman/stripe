<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="chat_message")
 */
class ChatMessage
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /** @ORM\Column(type="string", length=180) */
    private $siteKey;

    /** @ORM\Column(type="string", length=180) */
    private $visitorId;

    /** @ORM\Column(type="string", length=20) */
    private $sender;

    /** @ORM\Column(type="text") */
    private $message;

    /** @ORM\Column(type="datetime") */
    private $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId() { return $this->id; }
    public function getSiteKey() { return $this->siteKey; }
    public function setSiteKey($siteKey) { $this->siteKey = $siteKey; return $this; }
    public function getVisitorId() { return $this->visitorId; }
    public function setVisitorId($visitorId) { $this->visitorId = $visitorId; return $this; }
    public function getSender() { return $this->sender; }
    public function setSender($sender) { $this->sender = $sender; return $this; }
    public function getMessage() { return $this->message; }
    public function setMessage($message) { $this->message = $message; return $this; }
    public function getCreatedAt() { return $this->createdAt; }
}
