<?php

namespace App\Form;

use App\Entity\Civilite;
use App\Entity\Conversation;
use App\Entity\Pays;
use App\Entity\Role;
use App\Entity\User;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
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
            ->add('firstName', TextType::class, [
                'label' => 'Prénom *',
                'attr' => [
                    'placeholder' => 'Prénom *',

                ],
                'label_attr' => ['class' => 'label-none']

            ])
            ->add('name', TextType::class, [
                'label' => 'Nom *',
                'attr' => [
                    'placeholder' => 'Nom *',

                ],
                'label_attr' => ['class' => 'label-none']
            ])
            ->add('dateOfBirth', DateType::class, [
                    'widget' => 'choice',
                    'format' => 'dd-MM-yyyy',
                    'label' => 'Date de naissance *',
                    'row_attr' => ['class' => 'form-date-style'],
                    'years' => array_reverse(range(1900, date('Y'))),
                    'placeholder' => [
                        'year' => 'Année',
                        'month' => 'Mois',
                        'day' => 'Jour',
                    ],

                ]

            )
            ->add('phone', TelType::class, [
                'label' => 'Téléphone portable',
                'attr' => [
                    'placeholder' => 'Téléphone portable',

                ],
                'label_attr' => ['class' => 'label-none'],
                'required' => false,

            ])
            ->add('email', EmailType::class, [
                'label' => 'Email *',
                'attr' => [
                    'placeholder' => 'Email *',

                ],
                'label_attr' => ['class' => 'label-none']
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Mot de passe *',
                'attr' => [
                    'placeholder' => 'Mot de passe *',

                ],
                'label_attr' => ['class' => 'label-none']

            ])
            ->add('civilite', EntityType::class, [
                'class' => Civilite::class,
                'choice_label' => 'civiliteName',
                'multiple' => false,
                'label' => 'Votre civilité *',
                'row_attr' => ['class' => 'form-div-flex']
            ])
            ->add('pays', EntityType::class, [
                'class' => Pays::class,
                'choice_label' => 'namePays',
                'multiple' => false,
                'label' => 'Votre pays de résidence *',
                'row_attr' => ['class' => 'form-div-flex']
            ])
            ->add('inscriptionUser', SubmitType::class, [])
            ->add('deleteUser', SubmitType::class, []);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
