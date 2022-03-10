<?php

namespace App\Form;

use App\Entity\TodoListItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
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
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event): void {
            /** @var TodoListItem $item */
            $item = $event->getData();
            $form = $event->getForm();

            if (null === $item || null === $item->getId()) {
                $form
                    ->add('save', SubmitType::class, [
                        'attr' => [
                            'class' => 'btn btn-primary ms-2 text-dark',
                            'title' => 'Save',
                            ],
                        'label' => '💾',
                    ])
                    ->add('title', TextType::class, [
                        'attr' => [
                            'class' => 'list-group-item shadow-sm my-2 border-0',
                            'placeholder' => 'Click here to add a new task',
                        ],
                    ])
                ;

                return;
            }

            $form
                ->add('delete', SubmitType::class, [
                    'attr' => [
                        'class' => 'btn btn-danger ms-2 ',
                        'title' => 'Delete',
                    ],
                    'label' => '🗑',
                ])
                ->add('title', TextType::class, [
                    'attr' => [
                        'class' => 'list-group-item shadow-sm my-2 border-0 text-dark disabled',
                    ],
                ])
            ;
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TodoListItem::class,
        ]);
    }
}
