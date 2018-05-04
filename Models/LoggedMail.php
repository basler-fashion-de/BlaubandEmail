<?php

namespace BlaubandEmail\Models;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="blauband_email_logged_mail")
 * @ORM\HasLifecycleCallbacks
 */
class LoggedMail extends ModelEntity
{
    /**
     * @var integer $id
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="from_mail", type="string")
     */
    private $from;


    /**
     * @var string
     *
     * @ORM\Column(name="to_mail", type="string")
     */
    private $to;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string")
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text")
     */
    private $body;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_html", type="boolean")
     */
    private $isHtml;

    /**
     * @var int
     * @ORM\Column(name="orderID", type="integer", nullable=true)
     */
    private $orderId;

    /**
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Order\Order")
     * @ORM\JoinColumn(name="orderID", referencedColumnName="id")
     *
     * @var \Shopware\Models\Order\Order
     */
    private $order;

    /**
     * @var int
     * @ORM\Column(name="customerID", type="integer", nullable=true)
     */
    private $customerId;

    /**
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Customer\Customer")
     * @ORM\JoinColumn(name="customerID", referencedColumnName="id")
     *
     * @var \Shopware\Models\Order\Order
     */
    private $customer;

    /**
     * @var
     * @ORM\Column(name="create_date", type="datetime")
     */
    private $createDate;

    /**
     * Gets triggered only on insert
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createDate = new \DateTime("now");
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param string $from
     */
    public function setFrom(string $from)
    {
        $this->from = $from;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody(string $body)
    {
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param string $to
     */
    public function setTo(string $to)
    {
        $this->to = $to;
    }

    /**
     * @return bool
     */
    public function isHtml()
    {
        return $this->isHtml;
    }

    /**
     * @param bool $isHtml
     */
    public function setIsHtml(bool $isHtml)
    {
        $this->isHtml = $isHtml;
    }

    /**
     * @return \Shopware\Models\Order\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param \Shopware\Models\Order\Order $order
     */
    public function setOrder(\Shopware\Models\Order\Order $order)
    {
        $this->order = $order;
    }

    /**
     * @return mixed
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param mixed $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return mixed
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }
}