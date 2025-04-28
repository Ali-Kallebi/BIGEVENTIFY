<?php
namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom'
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom de famille'
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail',
                'required' => true,
                'empty_data' => '', // ✅ Assure un champ vide
                'attr' => [
                    'placeholder' => 'Adresse e-mail',
                    'autocomplete' => 'off' // ✅ Empêche le navigateur de compléter
                ]
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'mapped' => false, // ✅ Empêche la sauvegarde en BDD
                'required' => true,
                'empty_data' => '', // ✅ Assure un champ vide
                'attr' => [
                    'placeholder' => 'Mot de passe',
                    'autocomplete' => 'new-password' // ✅ Empêche le navigateur de remplir
                ]
            ])
            
            
            ->add('roles', ChoiceType::class, [
                'label' => 'Rôle',
                'choices' => User::getAvailableRoles(),
                'mapped' => false,
                'multiple' => false, // Dropdown
                'expanded' => false, // Sélection unique par liste déroulante
            ])
            
            
            
            ->add('submit', SubmitType::class, [
                'label' => 'S\'inscrire'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
