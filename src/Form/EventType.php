<?php
namespace App\Form;

use App\Entity\Event;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Récupérer l'option 'categories' passée depuis le contrôleur
        $categories = $options['categories'] ?? [];

        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => ['placeholder' => 'Entrez le titre de l\'événement'],
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'Description',
                'attr' => ['placeholder' => 'Décrivez l\'événement (optionnel)'],
            ])
            ->add('date', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Date et heure',
            ])
            ->add('location', TextType::class, [
                'label' => 'Lieu',
                'attr' => ['placeholder' => 'Entrez le lieu de l\'événement'],
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'choices' => $categories, // Utiliser les catégories passées en option
                'placeholder' => 'Sélectionner une catégorie',
                'label' => 'Catégorie',
                'required' => true,
                'attr' => ['class' => 'form-select shadow-sm rounded-3'],
            ])
            ->add('maxParticipants', IntegerType::class, [
                'label' => 'Nombre maximum de participants',
                'attr' => ['placeholder' => 'Entrez le nombre maximal de participants'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            'categories' => [], // Définir une valeur par défaut pour 'categories'
        ]);
    }
}
