<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Famille;
use Doctrine\ORM\EntityRepository;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            //->add('code')
            ->add('titre', TextType::class, [
                'attr' => ['class'=>'form-control', 'placeholder' => "Le nom de la catégorie", 'autocomplete'=>"off"]
            ])
            ->add('description', CKEditorType::class, ['attr'=>['class'=>'form-control']])
            ->add('media', FileType::class,[
                'attr'=>['class'=>"dropify", 'data-preview' => ".preview"],
                'label' => "Télécharger la photo d'illustration",
                'mapped' => false,
                'multiple' => false,
                'constraints' => [
                    new File([
                        'maxSize' => "20000k",
                        'mimeTypes' =>[
                            'image/png',
                            'image/jpeg',
                            'image/jpg',
                            'image/gif',
                            'image/webp',
                        ],
                        //'mimeTypesMessage' => "Votre fichier doit être de type image",
                        //'maxSizeMessage' => "La taille de votre image doit être inférieure à 2Mo",
                    ])
                ],
                'required' => false
            ])
            //->add('slug')
            //->add('createdAt')
            ->add('statut', CheckboxType::class,[
                'attr' => ['class' => 'custom-control-input'],
                'label_attr' => ['class' => 'custom-control-label'],
                'required' => false
            ])
            ->add('famille', EntityType::class,[
                'attr'=>['class'=>'form-control select2'],
                'class' => Famille::class,
                'query_builder' => function(EntityRepository $er){
                    return $er->createQueryBuilder('c')->orderBy('c.titre', "ASC");
                },
                'choice_label' => 'titre'
            ])
            ->add('tags', TextType::class,[
                'attr'=>['class'=>'form-control', 'data-role'=>'tagsinput'],
                'required' => true,
                'label' => "Mots clés"
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categorie::class,
        ]);
    }
}
