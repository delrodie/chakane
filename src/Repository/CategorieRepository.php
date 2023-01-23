<?php

namespace App\Repository;

use App\Entity\Categorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Categorie>
 *
 * @method Categorie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Categorie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Categorie[]    findAll()
 * @method Categorie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategorieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categorie::class);
    }

    public function save(Categorie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Categorie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findAllWithJoin(string $slug=null, string $genre=null, string $famille=null)
    {
        $query =  $this->createQueryBuilder('c')
            ->addSelect('f')
            ->addSelect('g')
            ->leftJoin('c.famille', 'f')
            ->leftJoin('c.genre', 'g')
            ;

        if ($slug){
            $query->where('c.slug LIKE :slug')
                ->setParameter('slug', "%{$slug}%");
        }

        if ($genre){
            $query->where('g.titre LIKE :genre')
                ->setParameter('genre', "%{$genre}%");
        }

        if ($famille){
            $query->where('f.titre LIKE :famille')
                ->setParameter('famille', "%{$famille}");
        }

        return $query->getQuery()->getResult();
    }

    public function findByGenreAndFamille(string $genre, string $famille)
    {
        return $this->createQueryBuilder('c')
            ->addSelect('f')
            ->addSelect('g')
            ->leftJoin('c.famille', 'f')
            ->leftJoin('c.genre', 'g')
            ->where('f.titre LIKE :famille')
            ->andWhere('g.titre LIKE :genre')
            ->orderBy('c.titre', "ASC")
            ->setParameters([
                'genre' => "%{$genre}%",
                'famille' => "%{$famille}%"
            ])
            ->getQuery()->getResult();
    }

//    /**
//     * @return Categorie[] Returns an array of Categorie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Categorie
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
