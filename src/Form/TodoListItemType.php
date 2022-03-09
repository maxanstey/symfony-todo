<?php

namespace App\Form;

use App\Entity\TodoListItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TodoListItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'attr' => [
                    'class' => 'list-group-item shadow-sm my-2 border-0',
                ],
            ])
            ->add('save', SubmitType::class, [ // TODO: change label and action based on if new or existing entity
                'attr' => [
                    'class' => 'btn btn-primary ms-2',
                    'placeholder' => 'Do the dishes',
                ],
                'label' => '💾',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TodoListItem::class,
        ]);
    }
}
