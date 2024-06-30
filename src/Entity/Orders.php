<?php

namespace App\Entity;

use App\Repository\OrdersRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use DateTime;

#[ORM\Entity(repositoryClass: OrdersRepository::class)]
#[ORM\Table(name: 'orders')]
class Orders
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'datetime')]
    private ?DateTime $date = null;


    #[ORM\OneToMany(targetEntity: OrdersItem::class, mappedBy:'order', orphanRemoval:true)]
    private $ordersItems;

    public function __construct()
    {
        $this->ordersItems = new ArrayCollection();
        $this->setDate();
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    #[ORM\PrePersist]
    public function setDate(): void
    {
        $this->date = new DateTime();
    }

    public function addOrdersItem(OrdersItem $orderItem): self
    {
        if (!$this->ordersItems->contains($orderItem)) {
            $this->ordersItems[] = $orderItem;
            $orderItem->setOrder($this);
        }

        return $this;
    }

    /**
     * @return Collection|OrdersItem[]
     */
    public function getOrdersItems(): Collection
    {
        return $this->ordersItems;
    }
}
