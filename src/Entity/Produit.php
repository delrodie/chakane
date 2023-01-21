<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $titre = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?int $cfaPrix = null;

    #[ORM\Column(nullable: true)]
    private ?int $cfaSolde = null;

    #[ORM\Column(nullable: true)]
    private ?float $usdPrix = null;

    #[ORM\Column(nullable: true)]
    private ?float $usdSolde = null;

    #[ORM\Column(nullable: true)]
    private ?float $euroPrix = null;

    #[ORM\Column(nullable: true)]
    private ?float $euroSolde = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $media = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tags = null;

    #[ORM\Column(nullable: true)]
    private ?bool $promotion = null;

    #[ORM\Column(nullable: true)]
    private ?int $niveau = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $slug = null;

    #[ORM\ManyToMany(targetEntity: Categorie::class, inversedBy: 'produits')]
    private Collection $categorie;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $taille = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $coleur = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(nullable: true)]
    private ?float $poids = null;

    public function __construct()
    {
        $this->categorie = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(?string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getMedia(): ?string
    {
        return $this->media;
    }

    public function setMedia(?string $media): self
    {
        $this->media = $media;

        return $this;
    }

    public function getTags(): ?string
    {
        return $this->tags;
    }

    public function setTags(?string $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function isPromotion(): ?bool
    {
        return $this->promotion;
    }

    public function setPromotion(?bool $promotion): self
    {
        $this->promotion = $promotion;

        return $this;
    }

    public function getNiveau(): ?int
    {
        return $this->niveau;
    }

    public function setNiveau(?int $niveau): self
    {
        $this->niveau = $niveau;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection<int, Categorie>
     */
    public function getCategorie(): Collection
    {
        return $this->categorie;
    }

    public function addCategorie(Categorie $categorie): self
    {
        if (!$this->categorie->contains($categorie)) {
            $this->categorie->add($categorie);
        }

        return $this;
    }

    public function removeCategorie(Categorie $categorie): self
    {
        $this->categorie->removeElement($categorie);

        return $this;
    }

    public function getTaille(): ?string
    {
        return $this->taille;
    }

    public function setTaille(?string $taille): self
    {
        $this->taille = $taille;

        return $this;
    }

    public function getColeur(): ?string
    {
        return $this->coleur;
    }

    public function setColeur(?string $coleur): self
    {
        $this->coleur = $coleur;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): \DateTime
    {
        return $this->createdAt = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): \DateTime
    {
        return $this->updatedAt = new \DateTime();
    }

    public function getPoids(): ?float
    {
        return $this->poids;
    }

    public function setPoids(?float $poids): self
    {
        $this->poids = $poids;

        return $this;
    }

    public function getCfaPrix(): ?int
    {
        return $this->cfaPrix;
    }

    public function setCfaPrix(?int $cfaPrix): self
    {
        $this->cfaPrix = $cfaPrix;

        return $this;
    }

    public function getCfaSolde(): ?int
    {
        return $this->cfaSolde;
    }

    public function setCfaSolde(?int $cfaSolde): self
    {
        $this->cfaSolde = $cfaSolde;

        return $this;
    }

    public function getUsdPrix(): ?float
    {
        return $this->usdPrix;
    }

    public function setUsdPrix(?float $usdPrix): self
    {
        $this->usdPrix = $usdPrix;

        return $this;
    }

    public function getUsdSolde(): ?float
    {
        return $this->usdSolde;
    }

    public function setUsdSolde(?float $usdSolde): self
    {
        $this->usdSolde = $usdSolde;

        return $this;
    }

    public function getEuroPrix(): ?float
    {
        return $this->euroPrix;
    }

    public function setEuroPrix(?float $euroPrix): self
    {
        $this->euroPrix = $euroPrix;

        return $this;
    }

    public function getEuroSolde(): ?float
    {
        return $this->euroSolde;
    }

    public function setEuroSolde(?float $euroSolde): self
    {
        $this->euroSolde = $euroSolde;

        return $this;
    }
}
