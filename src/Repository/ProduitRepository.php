<?php

namespace App\Repository;

use App\Entity\Produit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Produit>
 *
 * @method Produit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Produit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Produit[]    findAll()
 * @method Produit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Produit::class);
    }

    public function save(Produit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Produit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByFamille($famille, $genre=null)
    {
        $query = $this->createQueryBuilder('p')
            ->addSelect('c')
            ->addSelect('f')
            ->addSelect('g')
            ->leftJoin('p.categorie', 'c')
            ->leftJoin('c.genre', 'g')
            ->leftJoin('c.famille', 'f')
            ;
        if ($famille && $genre){
            $query->where('f.slug LIKE :famille')
                ->andWhere('g.titre LIKE :genre')
                ->setParameters([
                    'famille' => "%{$famille}%",
                    'genre' => "%{$genre}%"
                ]);
        }elseif ($famille){
            $query->where('f.titre LIKE :famille')
                ->setParameter('famille', "%{$famille}%");
        }elseif ($genre){
            $query->where('g.titre LIKE :genre')
                ->setParameter('genre', "%{$genre}%");
        }else{
            $query;
        }
        $query->orderBy('p.promotion', "DESC")
            ->addOrderBy('p.niveau', "DESC");

        return $query->getQuery()->getResult();
    }
    public function findByCategorie(string $categorie)
    {
        return $this->createQueryBuilder('p')
            ->addSelect('c')
            ->addSelect('f')
            ->addSelect('g')
            ->leftJoin('p.categorie', 'c')
            ->leftJoin('c.famille', 'f')
            ->leftJoin('c.genre', 'g')
            ->where('c.slug = :slug')
            ->orderBy('p.promotion', 'DESC')
            ->addOrderBy('p.niveau', 'DESC')
            ->setParameter('slug', $categorie)
            ->getQuery()->getResult()
            ;
    }

//    /**
//     * @return Produit[] Returns an array of Produit objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Produit
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

}
