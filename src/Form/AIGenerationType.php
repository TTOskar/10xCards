<?php

namespace App\Form;

use App\DTO\Request\AI\GenerateFlashcardsRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class AIGenerationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('input_text', TextareaType::class, [
                'label' => 'Tekst do przetworzenia',
                'attr' => [
                    'rows' => 10,
                    'class' => 'bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5',
                    'placeholder' => 'Wklej tutaj tekst do przetworzenia (minimum 1000 znaków)',
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Tekst nie może być pusty.',
                    ]),
                    new Assert\Length([
                        'min' => 1000,
                        'max' => 10000,
                        'minMessage' => 'Tekst musi mieć co najmniej {{ limit }} znaków.',
                        'maxMessage' => 'Tekst nie może być dłuższy niż {{ limit }} znaków.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[\p{L}\p{N}\p{P}\s]+$/u',
                        'message' => 'Tekst może zawierać tylko litery, cyfry, znaki interpunkcyjne i spacje.',
                    ]),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Generate Flashcards',
                'attr' => [
                    'class' => 'text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-700 focus:outline-none dark:focus:ring-blue-800',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GenerateFlashcardsRequest::class,
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id' => 'ai_generation',
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'ai_generation';
    }
} 