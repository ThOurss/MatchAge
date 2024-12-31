<?php

namespace App\Form;

use App\Entity\MatchAccept;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MatchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('AccepteMatch', SubmitType::class, [
                'label' => 'Accepter le match',
            ])
            ->add('RefuserMatch', SubmitType::class, [
                'label' => 'Refuser le match',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MatchAccept::class,
        ]);
    }
}