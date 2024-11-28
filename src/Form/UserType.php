<?php

namespace App\Form;

use App\Entity\Civilite;
use App\Entity\Conversation;
use App\Entity\Pays;
use App\Entity\Role;
use App\Entity\User;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName',TextType::class,[
                'label' => 'Prénom *',


            ])
            ->add('name',TextType::class,[
                'label' => 'Nom *',
            ])
            ->add('dateOfBirth', DateType::class,[
                'label' => 'Date de naissance *',
                ]

            )
            ->add('phone',TelType::class,[
                'label'=> 'Téléphone portable',
                'required' => false,

            ])
            ->add('email',EmailType::class,[
                'label' => 'Email *',
            ])
            ->add('password',PasswordType::class,[
                'label' => 'Mot de passe *',

            ])

            ->add('civilite', EntityType::class, [
                'class' => Civilite::class,
                'choice_label' => 'civiliteName',
                'multiple' => false,
                'label' => 'Votre civilité *',
            ])
            ->add('pays', EntityType::class, [
                'class' => Pays::class,
                'choice_label' => 'namePays',
                'multiple' => false,
                'label' => 'Votre pays de résidence *',
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
