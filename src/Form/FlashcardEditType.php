<?php

namespace App\Form;

use App\DTO\FlashcardUpdateDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class FlashcardEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('front', TextType::class, [
                'label' => 'Przód fiszki',
                'attr' => [
                    'class' => 'bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5',
                    'placeholder' => 'Wprowadź tekst na przodzie fiszki',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Przód fiszki nie może być pusty.',
                    ]),
                    new Assert\Length([
                        'max' => 200,
                        'maxMessage' => 'Przód fiszki nie może być dłuższy niż {{ limit }} znaków.',
                    ]),
                ],
            ])
            ->add('back', TextareaType::class, [
                'label' => 'Tył fiszki',
                'attr' => [
                    'rows' => 4,
                    'class' => 'bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5',
                    'placeholder' => 'Wprowadź tekst na tyle fiszki',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Tył fiszki nie może być pusty.',
                    ]),
                    new Assert\Length([
                        'max' => 1000,
                        'maxMessage' => 'Tył fiszki nie może być dłuższy niż {{ limit }} znaków.',
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FlashcardUpdateDTO::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'flashcard_edit',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'flashcard_edit';
    }
} 